<?php
include('config.php');
session_start();

if ($_SESSION['role'] == 'Admin') {
    $result = mysqli_query($conn, "SELECT * FROM fraud_logs");
    echo "<table>";
    echo "<tr><th>ID</th><th>Details</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>".$row['id']."</td><td>".$row['details']."</td></tr>";
    }
    echo "</table>";
} else {
    echo "Access denied.";
}
?>
