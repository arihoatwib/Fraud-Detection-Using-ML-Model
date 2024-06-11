# app.py
# Import necessary modules
import os
import sqlite3
import time
from flask import Flask, render_template, redirect, url_for, session, request, flash
from flask_wtf import FlaskForm
from wtforms import StringField, PasswordField, SubmitField
from wtforms.validators import DataRequired, Length, EqualTo
from flask_login import LoginManager, UserMixin, login_user, login_required, logout_user, current_user
from werkzeug.security import generate_password_hash, check_password_hash
from werkzeug.utils import secure_filename
import joblib

# Import the authentication blueprint
from fraud_detection_model.auth import auth_bp

# Load the pre-trained model
model = joblib.load('model.joblib')

# Initialize the Flask app
app = Flask(__name__)
app.secret_key = os.urandom(24)

# Register the authentication blueprint
app.register_blueprint(auth_bp)

# Initialize Flask-Login
login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = 'login'

# Define the database path
DB_PATH = r'D:\Sastra_MCA\Sem IV\Fraud_Detection_Model\fraud_detection_model\users.db'

# Define the upload folder for profile pictures
UPLOAD_FOLDER = 'static/profile_pics'
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif'}

# Configure the upload folder
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

# Ensure the upload folder exists
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

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
                 (id INTEGER PRIMARY KEY, username TEXT UNIQUE, password TEXT, profile_pic TEXT)''')
    conn.commit()
    conn.close()

create_table()

# Define a User class for Flask-Login
class User(UserMixin):
    def __init__(self, id, username, profile_pic):
        self.id = id
        self.username = username
        self.profile_pic = profile_pic

# Define the user loader function
@login_manager.user_loader
def load_user(user_id):
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("SELECT id, username, password, COALESCE(profile_pic, '') FROM users WHERE id = ?", (user_id,))
    user_data = c.fetchone()
    conn.close()
    if user_data:
        return User(user_data[0], user_data[1], user_data[3])
    return None

# Define the registration form
class RegistrationForm(FlaskForm):
    username = StringField('Username', validators=[DataRequired(), Length(min=4, max=20)])
    password = PasswordField('Password', validators=[DataRequired(), Length(min=8)])
    confirm_password = PasswordField('Confirm Password', validators=[DataRequired(), EqualTo('password')])
    submit = SubmitField('Sign Up')

# Define the login form
class LoginForm(FlaskForm):
    username = StringField('Username', validators=[DataRequired(), Length(min=4, max=20)])
    password = PasswordField('Password', validators=[DataRequired()])
    submit = SubmitField('Log In')

# Define a function to check if a file has an allowed extension
def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

# Define the main route
@app.route('/')
@login_required
def main():
    user_initials = current_user.username[0].upper()  # Get user's initial for display
    return render_template('index.html', user_name=current_user.username, user_initials=user_initials, profile_pic=current_user.profile_pic)

# Define the login route
@app.route('/login', methods=['GET', 'POST'])
def login():
    form = LoginForm()
    if form.validate_on_submit():
        username = form.username.data
        password = form.password.data
        conn = sqlite3.connect(DB_PATH)
        c = conn.cursor()
        c.execute("SELECT id, username, password, COALESCE(profile_pic, '') FROM users WHERE username = ?", (username,))
        user_data = c.fetchone()
        conn.close()
        if user_data and check_password_hash(user_data[2], password):
            # Login successful, set the user ID in the session
            session['user_id'] = user_data[0]
            flash('Login successful!')
            # Redirect to the main page
            return redirect(url_for('main'))
        else:
            flash('Invalid username or password.')
    return render_template('login.html', form=form)

# Define the dashboard route
@app.route('/dashboard')
@login_required
def dashboard():
    return "Welcome to the Dashboard!"

# Define the registration route
@app.route('/register', methods=['GET', 'POST'])
def register():
    form = RegistrationForm()
    if form.validate_on_submit():
        username = form.username.data
        password = form.password.data
        hashed_password = generate_password_hash(password, method='pbkdf2:sha256')
        conn = sqlite3.connect(DB_PATH)
        c = conn.cursor()
        c.execute("INSERT INTO users (username, password) VALUES (?, ?)", (username, hashed_password))
        conn.commit()
        conn.close()
        flash('Registration successful')
        return redirect(url_for('login'))  # Redirect to login

    # If the form is not submitted or has validation errors, render the registration form
    return render_template('register.html', form=form)

if __name__ == '__main__':
    app.run(debug=True)
