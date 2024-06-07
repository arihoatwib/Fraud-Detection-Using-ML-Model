<?php
include '../includes/header.php';
include '../includes/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $log_type = $_POST['log_type'];
    $timestamp = date("Y-m-d H:i:s");

    if ($log_type == 'transaction') {
        $transaction_id = $_POST['transaction_id'];
        $amount = $_POST['amount'];
        $user_id = $_POST['user_id'];
        $query = "INSERT INTO transactions (transaction_id, amount, user_id, timestamp) VALUES ('$transaction_id', '$amount', '$user_id', '$timestamp')";
    } elseif ($log_type == 'fraud') {
        $fraud_id = $_POST['fraud_id'];
        $transaction_id = $_POST['transaction_id'];
        $reason = $_POST['reason'];
        $query = "INSERT INTO frauds (fraud_id, transaction_id, reason, timestamp) VALUES ('$fraud_id', '$transaction_id', '$reason', '$timestamp')";
    }

    if (mysqli_query($conn, $query)) {
        echo ucfirst($log_type) . " logged successfully.";
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}
?>

<!-- HTML Form for logging transactions and frauds -->
<div class="container">
    <h1>Log Transaction/Fraud</h1>
    <form method="post" action="log.php">
        <select name="log_type" required>
            <option value="transaction">Transaction</option>
            <option value="fraud">Fraud</option>
        </select>
        <input type="text" name="transaction_id" placeholder="Transaction ID" required>
        <input type="number" name="amount" placeholder="Amount">
        <input type="text" name="user_id" placeholder="User ID">
        <input type="text" name="fraud_id" placeholder="Fraud ID">
        <input type="text" name="reason" placeholder="Reason">
        <button type="submit">Log</button>
    </form>
</div>
