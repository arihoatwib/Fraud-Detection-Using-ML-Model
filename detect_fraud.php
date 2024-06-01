<?php

// Function to detect fraud using a machine learning model
function detectFraud($transactionData) {
    // Convert the transaction data to a JSON string
    $jsonData = json_encode($transactionData);

    // Save the JSON data to a file
    file_put_contents('transaction_data.json', $jsonData);

    // Call the Python script to make a prediction
    $command = escapeshellcmd('python3 fraud_model.py transaction_data.json');
    $output = shell_exec($command);

    // Decode the JSON output from the Python script
    $result = json_decode($output, true);

    // Return the result
    return $result['fraud_status'];
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get transaction data from the form
    $transactionData = array(
        'amount' => $_POST["transaction_amount"],
        // Add more transaction data fields here if needed
        'transaction_id' => $_POST["transaction_id"],
        'user_id' => $_POST["user_id"],
        'timestamp' => $_POST["timestamp"],
        // Add any additional features your model requires
    );

    // Call the function to detect fraud
    $fraud_status = detectFraud($transactionData);

    // Display the result
    echo "<h2>Result:</h2>";
    echo "<p>" . $fraud_status . "</p>";
}

?>
