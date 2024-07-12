from flask import Blueprint, render_template, request, redirect, url_for, flash
from werkzeug.security import generate_password_hash
from flask_mail import Mail, Message
from .users import reset_password, update_reset_token, generate_reset_token, is_valid_reset_token
import sqlite3
import os

auth_bp = Blueprint('auth', __name__)

# Define the database path
DB_PATH = r'D:\Sastra_MCA\Sem IV\Fraud_Detection_Model\fraud_detection_model\users.db'

# Function to connect to the SQLite database
def get_db_connection():
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    return conn

# Configure Flask-Mail
mail = Mail()

def init_mail(app):
    app.config.update(
        MAIL_SERVER='smtp.gmail.com',
        MAIL_PORT=587,
        MAIL_USE_TLS=True,
        MAIL_USE_SSL=False,
        MAIL_USERNAME=os.getenv('MAIL_USERNAME'),
        MAIL_PASSWORD=os.getenv('MAIL_PASSWORD'),
        MAIL_DEFAULT_SENDER=('System Administrator', os.getenv('MAIL_USERNAME'))
    )
    mail.init_app(app)

@auth_bp.route('/forgot-password', methods=['GET', 'POST'])
def forgot_password_route():
    if request.method == 'POST':
        email = request.form['email']
        conn = get_db_connection()
        user = conn.execute('SELECT * FROM users WHERE email = ?', (email,)).fetchone()
        if user:
            token = generate_reset_token()
            update_reset_token(user['username'], token)
            reset_url = url_for('auth.reset_password_route', token=token, _external=True)
            
            # Send the reset email here with reset_url
            msg = Message('Password Reset Request', recipients=[email])
            msg.body = f'Please click the link to reset your password: {reset_url}'
            mail.send(msg)
            
            flash('Check your email for a password reset link.', 'info')
        else:
            flash('Email address not found.', 'danger')
        conn.close()
        return redirect(url_for('login'))
    return render_template('forgot_password.html')

@auth_bp.route('/reset-password/<token>', methods=['GET', 'POST'])
def reset_password_route(token):
    conn = get_db_connection()
    user = conn.execute('SELECT username FROM users WHERE reset_token = ?', (token,)).fetchone()
    
    if user and is_valid_reset_token(user['username'], token):
        username = user['username']
    else:
        flash('The password reset link is invalid or expired.', 'danger')
        return redirect(url_for('auth.forgot_password_route'))

    if request.method == 'POST':
        new_password = request.form['password']
        hashed_password = generate_password_hash(new_password)  # Hash the new password
        reset_password(username, hashed_password)  # Update the password in the database
        flash('Your password has been updated!', 'success')
        return redirect(url_for('login'))  # Redirect to login route after password reset

    return render_template('reset_password.html', token=token)
