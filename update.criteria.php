<?php

// Example: Update Fraud Detection Criteria

// Function to update fraud detection thresholds
function updateThreshold($new_threshold) {
    // Save the new threshold to a configuration file
    $config = array('threshold' => $new_threshold);
    file_put_contents('config.json', json_encode($config));
}

// Function to retrain the model with new data
function retrainModel() {
    // Call a Python script to retrain the model
    $command = escapeshellcmd('python3 retrain_model.py');
    $output = shell_exec($command);
    return $output;
}

// Check if the form is submitted to update criteria
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["threshold"])) {
        // Update the threshold value
        $new_threshold = $_POST["threshold"];
        updateThreshold($new_threshold);
        echo "Threshold updated successfully.";
    }

    if (isset($_POST["retrain_model"])) {
        // Retrain the model
        $retrain_output = retrainModel();
        echo "Model retrained successfully. Output: " . $retrain_output;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Fraud Detection Criteria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Update Fraud Detection Criteria</h1>
        <form action="update_criteria.php" method="post">
            <label for="threshold">New Threshold:</label>
            <input type="text" id="threshold" name="threshold">
            <button type="submit">Update Threshold</button>
        </form>
        <form action="update_criteria.php" method="post">
            <input type="hidden" name="retrain_model" value="1">
            <button type="submit">Retrain Model</button>
        </form>
    </div>
</body>
</html>
