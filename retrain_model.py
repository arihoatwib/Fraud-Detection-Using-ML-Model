import joblib
import pandas as pd
from sklearn.ensemble import RandomForestClassifier

# Load new training data
# This could be from a database, file, or any other data source
data = pd.read_csv('new_training_data.csv')
X = data.drop('fraud', axis=1)
y = data['fraud']

# Initialize and train the model
model = RandomForestClassifier()
model.fit(X, y)

# Save the retrained model
joblib.dump(model, 'fraud_detection_model.pkl')

print("Model retrained successfully")
