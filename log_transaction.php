<?php
include '../includes/header.php';
include '../includes/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $amount = $_POST['amount'];
    $user_id = $_POST['user_id'];
    $timestamp = date("Y-m-d H:i:s");

    $query = "INSERT INTO transactions (transaction_id, amount, user_id, timestamp) VALUES ('$transaction_id', '$amount', '$user_id', '$timestamp')";
    mysqli_query($conn, $query);
    echo "Transaction logged successfully.";
}
?>

<div class="container">
    <h1>Log Transaction</h1>
    <form method="post" action="">
        <label for="transaction_id">Transaction ID:</label>
        <input type="text" id="transaction_id" name="transaction_id" required>
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" required>
        <label for="user_id">User ID:</label>
        <input type="text" id="user_id" name="user_id" required>
        <button type="submit">Log Transaction</button>
    </form>
</div>

<?php
include '../includes/footer.php';
?>
