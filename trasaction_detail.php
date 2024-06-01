<?php
// Include database connection and session start
include('config.php');
session_start();

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if transaction ID is provided in the URL
if (!isset($_GET['id'])) {
    header("Location: transaction_monitoring_dashboard.php");
    exit();
}

// Fetch transaction details from the database based on ID
$transaction_id = $_GET['id'];
$query = "SELECT * FROM transactions WHERE id = $transaction_id";
$result = mysqli_query($conn, $query);

// Check if transaction is found
if (mysqli_num_rows($result) == 1) {
    // Display transaction details
    $transaction = mysqli_fetch_assoc($result);
    echo "<h2>Transaction Details</h2>";
    echo "<p><strong>Transaction ID:</strong> ".$transaction['id']."</p>";
    echo "<p><strong>User ID:</strong> ".$transaction['user_id']."</p>";
    echo "<p><strong>Amount:</strong> ".$transaction['amount']."</p>";
    echo "<p><strong>Timestamp:</strong> ".$transaction['timestamp']."</p>";
    
    // Additional actions like approve, reject, or escalate the transaction can be implemented here
    echo "<form method='post' action='process_transaction.php'>";
    echo "<input type='hidden' name='transaction_id' value='".$transaction['id']."'>";
    echo "<input type='hidden' name='action' value=''>";
    echo "<select name='action'>";
    echo "<option value='approve'>Approve</option>";
    echo "<option value='reject'>Reject</option>";
    echo "<option value='escalate'>Escalate</option>";
    echo "</select>";
    echo "<button type='submit'>Submit</button>";
    echo "</form>";
} else {
    echo "Transaction not found.";
}

// Close database connection
mysqli_close($conn);
?>
