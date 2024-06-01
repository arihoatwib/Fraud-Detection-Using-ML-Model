<?php
include '../includes/header.php';
include '../includes/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $query = "SELECT * FROM transactions WHERE timestamp BETWEEN '$start_date' AND '$end_date'";
    $result = mysqli_query($conn, $query);
    ?>

    <div class="container">
        <h1>Generated Report</h1>
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
?>
    <div class="container">
        <h1>Generate Report</h1>
        <form method="post" action="">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>
            <button type="submit">Generate Report</button>
        </form>
    </div>
<?php
}
include '../includes/footer.php';
?>
