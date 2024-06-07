<?php
include '../includes/header.php';
include '../includes/database.php';
session_start();

if ($_SESSION['role'] == 'Admin') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $query = "SELECT * FROM transactions WHERE timestamp BETWEEN '$start_date' AND '$end_date'";
        $result = mysqli_query($conn, $query);
    } else {
        $query = "SELECT * FROM fraud_logs";
        $result = mysqli_query($conn, $query);
    }
?>

<div class="container">
    <h1>Reports</h1>

    <!-- Generate Report Form -->
    <form method="post" action="reports.php">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>
        <button type="submit">Generate Report</button>
    </form>

    <!-- Display Report -->
    <table>
        <tr>
            <th>Transaction ID</th>
            <th>Amount</th>
            <th>User ID</th>
            <th>Timestamp</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['transaction_id']; ?></td>
            <td><?php echo $row['amount']; ?></td>
            <td><?php echo $row['user_id']; ?></td>
            <td><?php echo $row['timestamp']; ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

<?php
} else {
    echo "Access denied.";
}

include '../includes/footer.php';
?>
