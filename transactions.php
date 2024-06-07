<?php
include('config.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Process payment if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay'])) {
    $paymentDetails = $_POST['paymentDetails'];

    // Send payment details to backend for processing
    $isFraud = checkForFraud($paymentDetails);

    // Log transaction and fraud status
    logTransaction($paymentDetails, $isFraud);

    // Provide feedback to user
    if ($isFraud) {
        $feedback = "Transaction flagged as fraudulent.";
    } else {
        $feedback = "Transaction processed successfully.";
    }
}

function checkForFraud($details) {
    // Secure API integration with machine learning model for fraud detection
    $api_url = "https://api.fraudmodel.com/check";
    $api_key = "your_api_key";
    $ch = curl_init($api_url);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($details));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['isFraud'];
}

function logTransaction($details, $isFraud) {
    global $db;
    $status = $isFraud ? 'Fraudulent' : 'Not Fraudulent';
    $stmt = $db->conn->prepare("INSERT INTO transactions (user_id, details, status) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $_SESSION['user_id'], json_encode($details), $status);
    $stmt->execute();
    $stmt->close();
}
?>

<!-- HTML for Transaction Monitoring and Processing -->
<div class="container">
    <h1>Transaction Monitoring Dashboard</h1>
    <table>
        <tr>
            <th>Transaction ID</th>
            <th>Amount</th>
            <th>User ID</th>
            <th>Timestamp</th>
        </tr>
        <?php
        $query = "SELECT * FROM transactions";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['transaction_id'] . "</td>";
            echo "<td>" . $row['amount'] . "</td>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . $row['timestamp'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>

    <h2>Process Payment</h2>
    <form method="post" action="transactions.php">
        <label for="paymentDetails">Payment Details:</label>
        <input type="text" name="paymentDetails" id="paymentDetails" required>
        <button type="submit" name="pay" class="btn btn-primary">Submit Payment</button>
    </form>
    <?php if (isset($feedback)): ?>
        <div class="alert alert-info">
            <?php echo $feedback; ?>
        </div>
    <?php endif; ?>
</div>
