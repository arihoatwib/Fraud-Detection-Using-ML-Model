import sqlite3
from datetime import datetime, timedelta
import secrets
from flask import flash
# Path to the database file
DB_PATH = r'D:\Sastra_MCA\Sem IV\Fraud_Detection_Model\fraud_detection_model\users.db'

# Function to connect to the SQLite database
def connect_db():
    return sqlite3.connect(DB_PATH)

# Function to create the users table if it doesn't exist
def create_table():
    conn = connect_db()
    c = conn.cursor()
    # Create the users table if it doesn't exist
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

# Function to create the orders table
def create_orders_table():
    conn = connect_db()
    c = conn.cursor()
    # Create the users table if it doesn't exist
    c.execute('''CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id TEXT NOT NULL,
                service_name TEXT NOT NULL,
                quantity INTEGER NOT NULL,
                amount REAL NOT NULL,
                user_id INTEGER NOT NULL,
                order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(user_id) REFERENCES users(id)
            )''')
    conn.commit()
    conn.close()

# Function to create the payment table
def create_payment_table():
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    # Create the payments table if it doesn't exist
    c.execute('''CREATE TABLE IF NOT EXISTS payments
                 (id INTEGER PRIMARY KEY AUTOINCREMENT,
                 user_id INTEGER,
                 transaction_type TEXT NOT NULL,
                 amount REAL,
                 old_balance REAL,
                 new_balance REAL,
                 card_number TEXT,
                 expiry_date TEXT,
                 cvv TEXT,
                 email TEXT,
                 status TEXT,
                 timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                 FOREIGN KEY (user_id) REFERENCES users (id))''')
    
    conn.commit()
    conn.close()

# Function to create the transactions table
def create_transactions_table():
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute('''CREATE TABLE IF NOT EXISTS Transactions (
                 step INTEGER,
                 type TEXT,
                 amount FLOAT,
                 nameOrig TEXT,
                 oldbalanceOrg FLOAT,
                 newbalanceOrig FLOAT,
                 nameDest TEXT,
                 oldbalanceDest FLOAT,
                 newbalanceDest FLOAT,
                 isFraud INTEGER,
                 isFlaggedFraud INTEGER
                 )''')
    conn.commit()
    conn.close()

# Function to create the dashboard table
def create_dashboard_table():
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute('''CREATE TABLE IF NOT EXISTS dashboard (
                 id INTEGER PRIMARY KEY AUTOINCREMENT,
                 type TEXT,
                 amount FLOAT,
                 oldbalanceorg FLOAT,
                 newbalanceorg FLOAT,
                 prediction INTEGER
                 )''')
    conn.commit()
    conn.close()

# Function to check and add missing columns
def check_and_add_columns():
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("PRAGMA table_info(users);")
    columns = [column[1] for column in c.fetchall()]
    if 'email' not in columns:
        c.execute("ALTER TABLE users ADD COLUMN email TEXT UNIQUE;")
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
    expiry_time = datetime.now() + timedelta(hours=1)  # Set expiry time to 1 hour from now
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE username = ?", (token, expiry_time, username))
    conn.commit()
    conn.close()

# Function to check if a reset token is valid for a user
def is_valid_reset_token(username, token):
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("SELECT reset_token, reset_token_expiry FROM users WHERE username = ?", (username,))
    result = c.fetchone()
    conn.close()
    
    if result:
        stored_token, expiry_time_str = result
        try:
            expiry_time = datetime.fromisoformat(expiry_time_str)  # Assuming expiry_time_str is in ISO format
        except ValueError:
            flash('Invalid expiry time format in the database.', 'danger')
            return False
        
        if stored_token == token and expiry_time > datetime.now():
            return True
        else:
            flash('The password reset link is invalid or expired.', 'danger')
            return False
    else:
        flash('User not found.', 'danger')
        return False

# Function to reset the password for a user
def reset_password(username, hashed_password):
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("UPDATE users SET password = ? WHERE username = ?", (hashed_password, username))
    conn.commit()
    conn.close()


# Initial setup
create_table()
create_orders_table()
create_payment_table()
check_and_add_columns()
create_transactions_table()
create_dashboard_table()
