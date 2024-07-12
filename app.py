# Import necessary modules
from flask import Flask, render_template, redirect, url_for, session, request, flash, jsonify,send_file, abort
from flask_login import LoginManager, UserMixin, login_user, login_required, logout_user, current_user
from werkzeug.security import generate_password_hash, check_password_hash
from wtforms import StringField, PasswordField, SubmitField, SelectField
from wtforms.validators import DataRequired, Length, Email, EqualTo
from logging.handlers import RotatingFileHandler
from fraud_detection_model.auth import auth_bp
from werkzeug.utils import secure_filename
from flask_socketio import SocketIO, emit
from flask_mail import Mail, Message
from flask_wtf import FlaskForm
from bs4 import BeautifulSoup
from functools import wraps
import xgboost as xgb
import pandas as pd
import numpy as np
import requests
import datetime
import logging
import sqlite3
import pdfkit
import joblib
import time
import os
import io

# Load the pre-trained model
model = joblib.load('model.joblib')

# Initialize the Flask app
app = Flask(__name__)
app.secret_key = os.urandom(24)
PAGE_SIZE = 20  # Number of transactions to fetch per page

# Configure Flask-Mail
app.config['MAIL_SERVER'] = 'smtp.gmail.com'
app.config['MAIL_PORT'] = 587
app.config['MAIL_USE_TLS'] = True
app.config['MAIL_USE_SSL'] = False
app.config['MAIL_USERNAME'] = os.getenv('MAIL_USERNAME')
app.config['MAIL_PASSWORD'] = 'ceso brjn lyjc zaoq'
app.config['MAIL_DEFAULT_SENDER'] = ('System Administrator', os.getenv('MAIL_USERNAME'))

# Initialize Flask-Mail
mail = Mail(app)
socketio = SocketIO(app)

# Register the authentication blueprint
app.register_blueprint(auth_bp)

# Initialize Flask-Login
login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = 'login'

# Define the database path
DB_PATH = r'D:\Sastra_MCA\Sem IV\Fraud_Detection_Model\fraud_detection_model\users.db'

# Define the upload folder for profile pictures
UPLOAD_FOLDER = 'fraud_detection_model/static/profile_pics'
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif'}

# Configure the upload folder
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

# Ensure the upload folder exists
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

# Define the upload folder for CSV files
CSV_UPLOAD_FOLDER = 'fraud_detection_model/uploads/'
app.config['CSV_UPLOAD_FOLDER'] = CSV_UPLOAD_FOLDER

# Ensure the upload folder for CSV files exists
os.makedirs(app.config['CSV_UPLOAD_FOLDER'], exist_ok=True)

# Define function to check if the file is a valid SQLite database
def is_valid_db(file_path):
    try:
        conn = sqlite3.connect(file_path)
        c = conn.cursor()
        c.execute("SELECT name FROM sqlite_master WHERE type='table';")
        conn.close()
        return True
    except sqlite3.DatabaseError:
        return False

# Delete the file if it's not a valid database
if os.path.exists(DB_PATH) and not is_valid_db(DB_PATH):
    try:
        os.remove(DB_PATH)
    except PermissionError:
        print(f"Failed to delete {DB_PATH}. File is in use by another process.")
        time.sleep(5)
        try:
            os.remove(DB_PATH)
        except PermissionError:
            print(f"Retry failed: Could not delete {DB_PATH}. Please close any other applications using this file.")

# Create the users table if it doesn't exist
def create_table():
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute('''CREATE TABLE IF NOT EXISTS users
                 (id INTEGER PRIMARY KEY AUTOINCREMENT, 
                 username TEXT NOT NULL UNIQUE, 
                 email TEXT UNIQUE, 
                 password TEXT NOT NULL, 
                 profile_pic TEXT,
                 reset_token TEXT, 
                 reset_token_expiry DATETIME,
                 role TEXT NOT NULL DEFAULT 'user')''')
    conn.commit()
    conn.close()

create_table()

# Define a User class for Flask-Login
class User(UserMixin):
    def __init__(self, id, username, profile_pic, role):
        self.id = id
        self.username = username
        self.profile_pic = profile_pic
        self.role = role

# Define the user loader function
@login_manager.user_loader
def load_user(user_id):
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("SELECT id, username, password, COALESCE(profile_pic, ''), role FROM users WHERE id = ?", (user_id,))
    user_data = c.fetchone()
    conn.close()
    if user_data:
        return User(user_data[0], user_data[1], user_data[3], user_data[4])  # Pass the role parameter
    return None

# Logging configuration
handler = RotatingFileHandler('app.log', maxBytes=10000, backupCount=1)
handler.setLevel(logging.INFO)
app.logger.addHandler(handler)

# Define the registration form
class RegistrationForm(FlaskForm):
    username = StringField('Username', validators=[DataRequired(), Length(min=4, max=20)])
    email = StringField('Email', validators=[DataRequired(), Email()])
    password = PasswordField('Password', validators=[DataRequired(), Length(min=8)])
    confirm_password = PasswordField('Confirm Password', validators=[DataRequired(), EqualTo('password')])
    role = SelectField('Role', choices=[('user', 'User')], validators=[DataRequired()])
    submit = SubmitField('Sign Up')

# Define the login form
class LoginForm(FlaskForm):
    username = StringField('Username', validators=[DataRequired(), Length(min=4, max=20)])
    password = PasswordField('Password', validators=[DataRequired()])
    submit = SubmitField('Log In')

# Define a function to check if a file has an allowed extension
def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

# Define the main route, point it to home_page.html
@app.route('/')
def home_page():
    return render_template('home_page.html')

# Define the login route
@app.route('/login', methods=['GET', 'POST'])
def login():
    form = LoginForm()
    if form.validate_on_submit():
        username = form.username.data
        password = form.password.data
        
        conn = sqlite3.connect(DB_PATH)
        c = conn.cursor()
        c.execute("SELECT id, username, password, COALESCE(profile_pic, ''), role FROM users WHERE username = ?", (username,))
        user_data = c.fetchone()
        conn.close()

        if user_data:
            stored_password_hash = user_data[2]
            user_role = user_data[4]  # Get the user's role

            print(f"Stored password hash: {stored_password_hash}")  # Debug statement
            print(f"Entered password: {password}")  # Debug statement

            if check_password_hash(stored_password_hash, password):
                # Create a user object and login the user
                user = User(id=user_data[0], username=user_data[1], profile_pic=user_data[3], role=user_role)
                login_user(user)
                flash('Login successful!')

                # Add session handling here
                session['user_id'] = user.id
                session['username'] = user.username
                session['role'] = user.role

                # Redirect based on the user's role
                if user_role == 'admin':
                    return redirect(url_for('admin_dashboard'))
                elif user_role == 'user':
                    return redirect(url_for('payment'))
            else:
                flash('Invalid password.')
        else:
            flash('Invalid username.')

    return render_template('login.html', form=form)

# Define the upload_profile_pic route
@app.route('/upload_profile_pic', methods=['POST'])
@login_required
def upload_profile_pic():
    if 'profile_pic' not in request.files:
        flash('No file part')
        return redirect(url_for('index'))
    
    file = request.files['profile_pic']
    if file.filename == '':
        flash('No selected file')
        return redirect(url_for('index'))
    
    if file and allowed_file(file.filename):
        filename = secure_filename(file.filename)
        file_path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        file.save(file_path)
        
        # Update user profile picture in the database
        conn = sqlite3.connect(DB_PATH)
        c = conn.cursor()
        c.execute("UPDATE users SET profile_pic = ? WHERE id = ?", (filename, current_user.id))
        conn.commit()
        conn.close()
        
        flash('Profile picture updated successfully')
        return redirect(url_for('index'))
    
    flash('Invalid file format')
    return redirect(url_for('index'))

# Define the registration route
@app.route('/register', methods=['GET', 'POST'])
def register():
    form = RegistrationForm()
    if form.validate_on_submit():
        username = form.username.data
        email = form.email.data
        password = form.password.data
        role = form.role.data
        hashed_password = generate_password_hash(password, method='pbkdf2:sha256')
        
        conn = sqlite3.connect(DB_PATH)
        c = conn.cursor()
        try:
            c.execute("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)", (username, email, hashed_password, role))
            conn.commit()
            
            # Set the first registered user as admin
            user_id = c.lastrowid
            if user_id == 1:
                c.execute("UPDATE users SET role = 'admin' WHERE id = ?", (user_id,))
                conn.commit()
                
            flash('Registration successful! Please log in.', 'success')
            return redirect(url_for('login'))
        except sqlite3.IntegrityError:
            flash('Username or email already exists.', 'danger')
        finally:
            conn.close()
    return render_template('register.html', form=form)

# Define the prediction route
@app.route('/prediction', methods=['POST'])
@login_required
def prediction():
    try:
        # Validate and convert inputs
        Trans_type = int(request.form['type'])
        Amount = float(request.form['amount'])
        OldbalanceOrg = float(request.form['oldbalanceOrg'])
        NewbalanceOrig = float(request.form['newbalanceOrig'])
        
        # Ensure the array is of the correct type
        arr = np.array([[Trans_type, Amount, OldbalanceOrg, NewbalanceOrig]], dtype=np.float32)

        # Predict
        pred = model.predict(arr)
        prediction_text = 'Fraud' if pred[0] == 1 else 'Not Fraud'

        return render_template('index.html', prediction_text=prediction_text)

    except ValueError as e:
        # Handle conversion errors
        return render_template('index.html', error=f"Input conversion error: {e}")

    except xgb.core.XGBoostError as e:
        # Specific handling for XGBoost errors
        return render_template('index.html', error=f"Model prediction error: {e}")

    except Exception as e:
        # General error handling
        return render_template('index.html', error=f"An unexpected error occurred: {e}")

# Define the Upload CSV route
@app.route('/upload', methods=['POST'])
def upload():
    if 'file' not in request.files:
        return "No file part", 400

    file = request.files['file']

    if file.filename == '':
        return "No selected file", 400

    if file and file.filename.endswith('.csv'):
        file_path = os.path.join(app.config['CSV_UPLOAD_FOLDER'], file.filename)
        file.save(file_path)
        
        df = pd.read_csv(file_path)
        conn = sqlite3.connect(DB_PATH)
        df.to_sql('Transactions', conn, if_exists='append', index=False)
        conn.close()
        return "File uploaded and data saved to database", 200
    return "Invalid file format", 400

# Predict route to predict transactions for fraud or not-fraud
@app.route('/predict', methods=['GET'])
def predict():
    try:
        conn = sqlite3.connect(DB_PATH)
        cursor = conn.cursor()
        
        # Fetch all transactions
        cursor.execute("SELECT * FROM Transactions")
        transactions = cursor.fetchall()
        
        # Define the columns for the DataFrame
        columns = ['step', 'type', 'amount', 'nameOrig', 'oldbalanceOrg', 'newbalanceOrig', 'nameDest', 'oldbalanceDest', 'newbalanceDest', 'isFraud', 'isFlaggedFraud']
        df = pd.DataFrame(transactions, columns=columns)

        # Map the 'type' column to numerical values for the model
        type_mapping = {"CASH_OUT": 1, "PAYMENT": 2, "CASH_IN": 3, "TRANSFER": 4, "DEBIT": 5}
        reverse_type_mapping = {v: k for k, v in type_mapping.items()}
        df["type"] = df["type"].map(type_mapping)

        # Ensure no NaN values are present after mapping
        if df["type"].isnull().any():
            return "Error: Invalid transaction types found in data.", 500

        # Prepare the feature matrix
        X = np.array(df[['type', 'amount', 'oldbalanceOrg', 'newbalanceOrig']], dtype=np.float32)

        # Predict using the pre-trained model
        predictions = model.predict(X)
        df['Prediction'] = predictions

        # Map the 'type' column back to categorical values for display
        df["type"] = df["type"].map(reverse_type_mapping)

        # Save predictions to dashboard table
        cursor.executemany("""
            INSERT INTO dashboard (type, amount, oldbalanceorg, newbalanceorg, prediction)
            VALUES (?, ?, ?, ?, ?)
        """, df[['type', 'amount', 'oldbalanceOrg', 'newbalanceOrig', 'Prediction']].values.tolist())
        
        conn.commit()
        
        # Fetch first 20 fraudulent transactions for immediate display to user
        cursor.execute("SELECT * FROM dashboard WHERE prediction = 1 LIMIT 20")
        fraud_transactions = cursor.fetchall()

        conn.close()

        # Convert fetched data to JSON serializable format
        fraud_transactions_list = []
        for transaction in fraud_transactions:
            transaction_dict = {
                'type': transaction[1],
                'amount': transaction[2],
                'oldbalanceorg': transaction[3],
                'newbalanceorg': transaction[4],
                'prediction': transaction[5]
            }
            fraud_transactions_list.append(transaction_dict)

        return jsonify(fraud_transactions_list), 200

    except Exception as e:
        print(f"Error during prediction process: {e}")
        return "An error occurred during prediction.", 500

# Define the get_fraudulent_transactions route
@app.route('/get_fraudulent_transactions', methods=['GET'])
def get_fraudulent_transactions():
    try:
        page = request.args.get('page', 1, type=int)
        conn = sqlite3.connect(DB_PATH)
        cursor = conn.cursor()

        offset = (page - 1) * PAGE_SIZE
        cursor.execute("SELECT * FROM dashboard WHERE prediction = 1 LIMIT ? OFFSET ?", (PAGE_SIZE, offset))
        fraudulent_transactions = cursor.fetchall()

        transactions = []
        for trans in fraudulent_transactions:
            transaction = {
                'type': trans[1],
                'amount': trans[2],
                'oldbalanceorg': trans[3],
                'newbalanceorg': trans[4],
                'prediction': trans[5]
            }
            transactions.append(transaction)

        conn.close()

        return jsonify({'transactions': transactions}), 200

    except Exception as e:
        print(f"Error fetching fraudulent transactions: {e}")
        return "An error occurred while fetching fraudulent transactions.", 500

# Define the dashboard_data fetch route
@app.route('/get_dashboard_data', methods=['GET', 'POST'])
def get_dashboard_data():
    try:
        if request.method == 'POST':
            data = request.get_json()
            if not data:
                raise ValueError("No data received or data format is incorrect.")

            print("Received table data:", data)  # Debugging line

            conn = sqlite3.connect(DB_PATH)
            cursor = conn.cursor()
            conn.close()

        # GET method: Fetch data for dashboard charts
        conn = sqlite3.connect(DB_PATH)
        cursor = conn.cursor()

        cursor.execute("SELECT COUNT(*) FROM dashboard WHERE prediction = 1")
        fraudulent_count = cursor.fetchone()[0]
        cursor.execute("SELECT COUNT(*) FROM dashboard WHERE prediction = 0")
        non_fraudulent_count = cursor.fetchone()[0]

        cursor.execute("SELECT type, COUNT(*) FROM dashboard GROUP BY type")
        type_analysis = cursor.fetchall()
        type_analysis_dict = {type: count for type, count in type_analysis}

        cursor.execute("""
            SELECT type, prediction, COUNT(*)
            FROM dashboard
            GROUP BY type, prediction
        """)
        type_fraud_analysis = cursor.fetchall()
        type_fraud_analysis_dict = {}
        for type, prediction, count in type_fraud_analysis:
            if type not in type_fraud_analysis_dict:
                type_fraud_analysis_dict[type] = [0, 0]
            type_fraud_analysis_dict[type][prediction] = count

        conn.close()

        return jsonify({
            'fraudulent_count': fraudulent_count,
            'non_fraudulent_count': non_fraudulent_count,
            'type_analysis': type_analysis_dict,
            'type_fraud_analysis': type_fraud_analysis_dict
        }), 200

    except ValueError as ve:
        print(f"ValueError: {ve}")
        return jsonify({"error": str(ve)}), 400
    except sqlite3.DatabaseError as db_err:
        print(f"DatabaseError: {db_err}")
        return jsonify({"error": "Database error occurred."}), 500
    except Exception as e:
        print(f"Error fetching dashboard data: {e}")
        return jsonify({"error": "An unexpected error occurred."}), 500

# Define the download report route
@app.route('/download_report', methods=['GET'])
def download_report():
    try:
        rendered_html = render_template('dashboard.html')
        # Extract only the printable content
        soup = BeautifulSoup(rendered_html, 'html.parser')
        printable_content = soup.find(id='printable-content')

        if printable_content is None:
            raise ValueError("Printable content not found in the HTML template.")

        # Convert the printable content to a string
        printable_html = str(printable_content)

        path_to_wkhtmltopdf = r'D:\Program Files\Wkhtmltopdf\bin\wkhtmltopdf.exe'
        config = pdfkit.configuration(wkhtmltopdf=path_to_wkhtmltopdf)

        options = {
            'no-outline': None,
            'disable-smart-shrinking': None,
            'quiet': ''
        }

        # Generate PDF
        pdf = pdfkit.from_string(printable_html, False, configuration=config, options=options)

        response = send_file(
            io.BytesIO(pdf),
            download_name='report.pdf',
            as_attachment=True
        )
        return response

    except ValueError as ve:
        print(f"ValueError: {ve}")
        return jsonify({"error": str(ve)}), 400
    except IOError as ioe:
        print(f"I/O Error during PDF generation: {ioe}")
        return jsonify({"error": "PDF generation error occurred."}), 500
    except Exception as e:
        print(f"Error during PDF generation: {e}")
        return jsonify({"error": "An unexpected error occurred."}), 500

# Define the admin_required decorator
def admin_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session or session.get('role') != 'admin':
            flash('You are not authorized to access this page.', 'danger')
            return redirect(url_for('login'))
        return f(*args, **kwargs)
    return decorated_function

# Define the user_required decorator
def user_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session or session.get('role') != 'user':
            flash('You are not authorized to access this page.', 'danger')
            return redirect(url_for('login'))
        return f(*args, **kwargs)
    return decorated_function

# Define the dashboard route
@app.route('/dashboard')
@login_required
@admin_required
def dashboard():
    return render_template('dashboard.html')

# Define the index route
@app.route('/index')
@login_required
@admin_required
def index():
    return render_template('index.html')

# Define the dash_board route
@app.route('/dash_board')
@login_required
@admin_required
def dash_board():
    if current_user.role == 'admin':
        return render_template('dash_board.html')
    return 'Access denied', 403

# Define the error handler
@app.errorhandler(401)
def unauthorized(error):
    flash('Unauthorized access. Please log in with appropriate credentials.', 'danger')
    return redirect(url_for('login'))

# Define the admin_dashboard route
@app.route('/admin_dashboard')
@login_required
@admin_required
def admin_dashboard():
    return render_template('admin_dashboard.html')

# Define the payment route
@app.route('/payment')
@login_required
def payment():
    return render_template('payment.html')

# Define the predict_payment route
@app.route('/predict_payment', methods=['POST'])
def predict_payment():
    data = request.get_json()
    type_mapping = {
        "CASH_OUT": 1,
        "PAYMENT": 2,
        "CASH_IN": 3,
        "TRANSFER": 4,
        "DEBIT": 5
    }
    
    try:
        # Convert the transaction type to its corresponding integer value
        transaction_type = data['transactionType']
        Trans_type = type_mapping.get(transaction_type)
        
        if Trans_type is None:
            raise ValueError(f"Invalid transaction type: {transaction_type}")

        Amount = float(data['amount'])
        OldbalanceOrg = float(data['oldBalance'])
        NewbalanceOrig = float(data['newBalance'])

        # Prepare the feature matrix
        arr = np.array([[Trans_type, Amount, OldbalanceOrg, NewbalanceOrig]], dtype=np.float32)
        pred = model.predict(arr)
        prediction = 'Fraud' if pred[0] == 1 else 'Not Fraud'

        # Log transaction
        app.logger.info(f"Transaction prediction: {prediction}")

        # Save transaction to database
        conn = sqlite3.connect(DB_PATH)
        c = conn.cursor()
        c.execute('''
            INSERT INTO payments (
                user_id, transaction_type, amount, old_balance, new_balance, 
                card_number, expiry_date, cvv, email, status, timestamp
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ''', (
            current_user.id, transaction_type, Amount, OldbalanceOrg, NewbalanceOrig, 
            data['cardNumber'], data['expiryDate'], data['cvv'], data['email'], prediction
        ))
        payment_id = c.lastrowid
        conn.commit()
        conn.close()

        # Send payment for processing
        response = requests.post(
            url_for('process_payment', _external=True),
            json={'id': payment_id, 'email': data['email']}
        )

        return jsonify(prediction=prediction, processing_status=response.json())
    except ValueError as e:
        return jsonify(error=f"Input conversion error: {e}")
    except xgb.core.XGBoostError as e:
        return jsonify(error=f"Model prediction error: {e}")
    except Exception as e:
        return jsonify(error=f"An unexpected error occurred: {e}")

# Define the process_payment route
@app.route('/process_payment', methods=['POST'])
def process_payment():
    data = request.get_json()
    try:
        app.logger.info("Processing payment for ID: %s", data['id'])

        # Retrieve the payment status from the payments table
        conn = sqlite3.connect(DB_PATH)
        c = conn.cursor()
        c.execute('SELECT status, email FROM payments WHERE id = ?', (data['id'],))
        result = c.fetchone()
        conn.close()

        if result is None:
            app.logger.error("Payment not found for ID: %s", data['id'])
            return jsonify(error='Payment not found'), 500

        payment_status = result[0]
        user_email = result[1]
        status = 'success'
        console_msg = ''

        app.logger.info("Payment status for ID %s: %s", data['id'], payment_status)

        # Send email to the user
        try:
            msg = Message('Transaction Status', sender=('System Administrator', os.getenv('MAIL_USERNAME')), recipients=[user_email])
            if payment_status == 'Fraud':
                msg.body = 'Your transaction has been flagged as fraudulent!!! Please contact us immediately.'
                status = 'Fraud'
                console_msg = 'Payment flagged Fraud. Contact HelpDesk Immediately.'
            else:
                msg.body = 'Your transaction has been processed successfully.'
            mail.send(msg)
            app.logger.info("Email sent to %s", user_email)
        except Exception as e:
            app.logger.error("Error sending email: %s", e)
            return jsonify(error=f"Error sending email: {e}")

        # Notify fraud analysts via WebSocket
        try:
            socketio.emit('transaction_update', {'status': status, 'details': {'payment_id': data['id']}}, broadcast=True)
            app.logger.info("WebSocket notification sent for payment ID %s", data['id'])
        except Exception as e:
            app.logger.error("Error emitting WebSocket notification: %s", e)
            return jsonify(error=f"Error emitting WebSocket notification: {e}")

        return jsonify(status=status, console_msg=console_msg)
    except Exception as e:
        app.logger.error("Unexpected error: %s", e)
        return jsonify(error=f"An unexpected error occurred: {e}")

# Define the analytics route
@app.route('/analytics')
@login_required
@admin_required
def analytics():
    if current_user.role != 'admin':
        return jsonify({'error': 'Access denied'}), 403

    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()

    try:
        # Daily totals
        c.execute('''SELECT COUNT(*), COALESCE(SUM(amount), 0), 
                     SUM(CASE WHEN status = 'Fraud' THEN 1 ELSE 0 END), 
                     COALESCE(SUM(CASE WHEN status = 'Fraud' THEN amount ELSE 0 END), 0), 
                     SUM(CASE WHEN status = 'Not Fraud' THEN 1 ELSE 0 END),
                     COALESCE(SUM(CASE WHEN status = 'Not Fraud' THEN amount ELSE 0 END), 0)
                     FROM payments WHERE DATE(timestamp) = DATE('now')''')
        daily_count, daily_total, daily_fraud_count, daily_fraud_total, daily_not_fraud_count, daily_not_fraud_total = c.fetchone()

        # Weekly totals
        c.execute('''SELECT COUNT(*), COALESCE(SUM(amount), 0), 
                     SUM(CASE WHEN status = 'Fraud' THEN 1 ELSE 0 END), 
                     COALESCE(SUM(CASE WHEN status = 'Fraud' THEN amount ELSE 0 END), 0), 
                     SUM(CASE WHEN status = 'Not Fraud' THEN 1 ELSE 0 END),
                     COALESCE(SUM(CASE WHEN status = 'Not Fraud' THEN amount ELSE 0 END), 0)
                     FROM payments WHERE DATE(timestamp) >= DATE('now', '-7 days')''')
        weekly_count, weekly_total, weekly_fraud_count, weekly_fraud_total, weekly_not_fraud_count, weekly_not_fraud_total = c.fetchone()

        # Monthly totals
        c.execute('''SELECT COUNT(*), COALESCE(SUM(amount), 0), 
                     SUM(CASE WHEN status = 'Fraud' THEN 1 ELSE 0 END), 
                     COALESCE(SUM(CASE WHEN status = 'Fraud' THEN amount ELSE 0 END), 0), 
                     SUM(CASE WHEN status = 'Not Fraud' THEN 1 ELSE 0 END),
                     COALESCE(SUM(CASE WHEN status = 'Not Fraud' THEN amount ELSE 0 END), 0)
                     FROM payments WHERE DATE(timestamp) >= DATE('now', 'start of month')''')
        monthly_count, monthly_total, monthly_fraud_count, monthly_fraud_total, monthly_not_fraud_count, monthly_not_fraud_total = c.fetchone()

        # Success and fraudulent payments
        c.execute('''SELECT status, COUNT(*), COALESCE(SUM(amount), 0) 
                     FROM payments GROUP BY status''')
        status_counts = c.fetchall()

        # Prepare data for JSON response
        analytics_data = {
            'daily_count': daily_count,
            'daily_total': daily_total,
            'daily_fraud_count': daily_fraud_count,
            'daily_fraud_total': daily_fraud_total,
            'daily_not_fraud_count': daily_not_fraud_count,
            'daily_not_fraud_total': daily_not_fraud_total,
            'weekly_count': weekly_count,
            'weekly_total': weekly_total,
            'weekly_fraud_count': weekly_fraud_count,
            'weekly_fraud_total': weekly_fraud_total,
            'weekly_not_fraud_count': weekly_not_fraud_count,
            'weekly_not_fraud_total': weekly_not_fraud_total,
            'monthly_count': monthly_count,
            'monthly_total': monthly_total,
            'monthly_fraud_count': monthly_fraud_count,
            'monthly_fraud_total': monthly_fraud_total,
            'monthly_not_fraud_count': monthly_not_fraud_count,
            'monthly_not_fraud_total': monthly_not_fraud_total,
            'status_counts': [{'status': row[0], 'count': row[1], 'amount': row[2]} for row in status_counts]
        }

        # If the request is via AJAX, return JSON
        if request.headers.get('Accept') == 'application/json':
            return jsonify(analytics_data)

        # Otherwise, render the HTML template
        return render_template('analytics.html', analytics_data=analytics_data)

    except Exception as e:
        return jsonify({'error': str(e)}), 500

    finally:
        conn.close()

# Define the get_flagged_payments route
@app.route('/get_flagged_payments', methods=['GET'])
def get_flagged_payments():
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    
    # Join the payments and users tables to get the username
    c.execute("""
        SELECT p.id, u.username, p.amount, p.card_number, p.status 
        FROM payments p
        JOIN users u ON p.user_id = u.id
        WHERE p.status = 'Fraud'
    """)
    flagged_payments = c.fetchall()
    conn.close()

    # Transform the data into a list of dictionaries
    payments_list = [{
        'id': payment[0],
        'user': payment[1],
        'amount': payment[2],
        'cardNumber': payment[3],
        'status': payment[4]
    } for payment in flagged_payments]

    return jsonify({'flaggedPayments': payments_list})

# Function to send email notifications
def send_email(recipient, subject, body):
    msg = Message(subject, sender=('System Administrator', os.getenv('MAIL_USERNAME')), recipients=[recipient])
    msg.body = body
    mail.send(msg)

# Define the update_payment_status route
@app.route('/update_payment_status', methods=['POST'])
def update_payment_status():
    data = request.json
    payment_id = data.get('id')
    action = data.get('status')  # This indicates the action taken by the admin (approve or reject)

    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("SELECT user_id, email, status FROM payments WHERE id = ?", (payment_id,))
    user_data = c.fetchone()

    if user_data:
        user_email = user_data[1]
        current_status = user_data[2]
        new_status = 'Not Fraud' if action == 'approved' else 'Fraud'

        if current_status == 'Fraud':
            c.execute("UPDATE payments SET status = ? WHERE id = ?", (new_status, payment_id))
            conn.commit()
            conn.close()

            # Send email notification to the user
            subject = f"Payment Status Update: {new_status.capitalize()}"
            body = f"Dear {user_data[0]},\n\nYour payment with ID {payment_id} has been {new_status}.\n\nRegards,\nRoyalTech Company (U) LTD"
            send_email(user_email, subject, body)

            return jsonify({'message': f'Payment status updated to {new_status} and user notified via email.'}), 200
        else:
            conn.close()
            return jsonify({'error': 'Payment status is not Fraud. Only Fraud payments can be approved or rejected.'}), 400
    else:
        conn.close()
        return jsonify({'error': 'Payment not found.'}), 404

# Define the save cart route
@app.route('/save_cart', methods=['POST'])
def save_cart():
    session['cart'] = request.json
    return jsonify({'status': 'success'})   

# Define the store_order_data route
@app.route('/store_order_data', methods=['POST'])
def store_order_data():
    if 'user_id' not in session:
        return redirect(url_for('login'))

    user_id = session['user_id']
    orders = request.json

    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()

    for order in orders:
        order_id = order['orderId']
        service_name = order['service']
        quantity = order['quantity']
        amount = order['price'] * quantity

        c.execute('''
            INSERT INTO orders (order_id, service_name, quantity, amount, user_id)
            VALUES (?, ?, ?, ?, ?)
        ''', (order_id, service_name, quantity, amount, user_id))

    conn.commit()
    conn.close()
    session.pop('cart', None)
    return jsonify({'status': 'success'}), 200

# Define the get__order_details route
@app.route('/get_order_details', methods=['GET'])
def get_order_details():
    if 'user_id' not in session:
        return jsonify({"error": "Not logged in"}), 401

    user_id = session['user_id']

    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute('''
        SELECT order_id, amount FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 1
    ''', (user_id,))
    order = c.fetchone()
    conn.close()

    if order:
        return jsonify({
            "orderId": order[0],
            "amount": order[1]
        })
    else:
        return jsonify({"error": "No orders found"}), 404

@socketio.on('connect')
def handle_connect():
    if current_user.is_authenticated and current_user.role == 'admin':
        emit('connected', {'data': 'Connected to real-time updates'})

# Define the logout route
@app.route('/logout')
@login_required
def logout():
    logout_user()
    session.pop('cart', None)  # Clear cart session on logout
    flash('You have been logged out.')
    return redirect(url_for('home_page'))

# Running the flask app
if __name__ == '__main__':
    socketio.run(app, debug=True)
