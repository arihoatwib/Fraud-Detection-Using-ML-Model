import json
import sys

import joblib  # or pickle, depending on how your model is saved

# Load the machine learning model
model = joblib.load('fraud_detection_model.pkl')

# Load transaction data from JSON file
with open(sys.argv[1], 'r') as f:
    transaction_data = json.load(f)

# Extract features from the transaction data
# Adjust this part to match your model's expected input
features = [
    transaction_data['amount'],
    transaction_data['transaction_id'],
    transaction_data['user_id'],
    transaction_data['timestamp'],
    # Add any additional features your model requires
]

# Make a prediction
prediction = model.predict([features])

# Translate the prediction to a human-readable format
fraud_status = "Suspicious Transaction Detected" if prediction[0] == 1 else "Transaction is Likely Not Fraudulent"

# Output the result as JSON
result = {'fraud_status': fraud_status}
print(json.dumps(result))
