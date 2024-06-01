<?php
// Database connection settings
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "fraud_detection";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get suspicious transactions
$sql = "SELECT * FROM transactions WHERE fraud_status = 'Suspicious Transaction Detected'";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Suspicious Transactions</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Suspicious Transactions</h1>
        <?php
        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Transaction ID</th><th>Amount</th><th>User ID</th><th>Timestamp</th><th>Status</th></tr>";
            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["transaction_id"] . "</td>";
                echo "<td>" . $row["amount"] . "</td>";
                echo "<td>" . $row["user_id"] . "</td>";
                echo "<td>" . $row["timestamp"] . "</td>";
                echo "<td>" . $row["fraud_status"] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No suspicious transactions found.</p>";
        }
        $conn->close();
        ?>
    </div>
</body>
</html>
