<?php
include '../includes/header.php';
include '../includes/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fraud_id = $_POST['fraud_id'];
    $transaction_id = $_POST['transaction_id'];
    $reason = $_POST['reason'];
    $timestamp = date("Y-m-d H:i:s");

    $query = "INSERT INTO frauds (fraud_id, transaction_id, reason, timestamp) VALUES ('$fraud_id', '$transaction_id', '$reason', '$timestamp')";
    mysqli_query($conn, $query);
    echo "Fraud case logged successfully.";
}
?>

<div class="container">
    <h1>Log Fraud Case</h1>
    <form method="post" action="">
        <label for="fraud_id">Fraud ID:</label>
        <input type="text" id="fraud_id" name="fraud_id" required>
        <label for="transaction_id">Transaction ID:</label>
        <input type="text" id="transaction_id" name="transaction_id" required>
        <label for="reason">Reason:</label>
        <input type="text" id="reason" name="reason" required>
        <button type="submit">Log Fraud Case</button>
    </form>
</div>

<?php
include '../includes/footer.php';
?>
