<?php
// Include database connection and session start
include('config.php');
session_start();

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch transaction data from the database
$query = "SELECT * FROM transactions";
$result = mysqli_query($conn, $query);

// Check if any transactions are found
if (mysqli_num_rows($result) > 0) {
    // Start building the HTML table
    echo "<table>";
    echo "<tr><th>Transaction ID</th><th>Amount</th><th>User ID</th><th>Timestamp</th></tr>";
    
    // Loop through each transaction and display it in the table
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td><a href='transaction_detail.php?id=".$row['id']."'>".$row['id']."</a></td>";
        echo "<td>".$row['amount']."</td>";
        echo "<td>".$row['user_id']."</td>";
        echo "<td>".$row['timestamp']."</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "No transactions found.";
}

// Close database connection
mysqli_close($conn);
?>
