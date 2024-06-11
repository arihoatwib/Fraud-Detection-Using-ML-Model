import sqlite3
from datetime import datetime, timedelta
import secrets

# Path to the database file
DB_PATH = r'D:\Sastra_MCA\Sem IV\Fraud_Detection_Model\fraud_detection_model\users.db'

# Function to connect to the SQLite database
def connect_db():
    return sqlite3.connect(DB_PATH)

# Function to create the users table if it doesn't exist
def create_table():
    conn = connect_db()
    c = conn.cursor()
    c.execute('''CREATE TABLE IF NOT EXISTS users
                 (id INTEGER PRIMARY KEY, username TEXT UNIQUE, password TEXT, profile_pic TEXT,
                 reset_token TEXT, reset_token_expiry DATETIME)''')
    conn.commit()
    conn.close()

# Function to check and add missing columns
def check_and_add_columns():
    conn = connect_db()
    c = conn.cursor()
    c.execute("PRAGMA table_info(users);")
    columns = [column[1] for column in c.fetchall()]
    if 'profile_pic' not in columns:
        c.execute("ALTER TABLE users ADD COLUMN profile_pic TEXT;")
    if 'reset_token' not in columns:
        c.execute("ALTER TABLE users ADD COLUMN reset_token TEXT;")
    if 'reset_token_expiry' not in columns:
        c.execute("ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME;")
    conn.commit()
    conn.close()

# Function to generate a reset token
def generate_reset_token():
    return secrets.token_urlsafe(16)

# Function to update the reset token and its expiry time for a user
def update_reset_token(username, token):
    expiry_time = datetime.now() + timedelta(hours=1)
    conn = connect_db()
    c = conn.cursor()
    c.execute("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE username = ?", (token, expiry_time, username))
    conn.commit()
    conn.close()

# Function to check if a reset token is valid for a user
def is_valid_reset_token(username, token):
    conn = connect_db()
    c = conn.cursor()
    c.execute("SELECT reset_token, reset_token_expiry FROM users WHERE username = ?", (username,))
    result = c.fetchone()
    conn.close()
    if result:
        stored_token, expiry_time = result
        if stored_token == token and expiry_time > datetime.now():
            return True
    return False

# Function to reset the password for a user
def reset_password(username, new_password):
    conn = connect_db()
    c = conn.cursor()
    c.execute("UPDATE users SET password = ? WHERE username = ?", (new_password, username))
    conn.commit()
    conn.close()

# Initial setup
create_table()
check_and_add_columns()
