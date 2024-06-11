from flask import Blueprint, render_template, request, redirect, url_for, flash
from .users import reset_password

auth_bp = Blueprint('auth', __name__)

@auth_bp.route('/forgot_password', methods=['GET', 'POST'])
def forgot_password_route():
    if request.method == 'POST':
        # Handle forgot password logic
        return redirect(url_for('login'))  # Redirect to login page
    return render_template('forgot_password.html')

@auth_bp.route('/reset_password/<token>', methods=['GET', 'POST'])
def reset_password_route(token):
    if request.method == 'POST':
        # Handle reset password logic
        return redirect(url_for('auth.login'))  # Redirect to login page
    return render_template('reset_password.html', token=token)
