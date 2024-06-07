<?php
include '../includes/header.php';
include '../includes/database.php';

$view_type = $_GET['view_type'];
if ($view_type == 'fraud') {
    $query = "SELECT * FROM frauds";
    $title = "All Fraud Cases";
} else {
    $query = "SELECT * FROM transactions";
    $title = "All Transactions";
}
$result = mysqli_query($conn, $query);
?>

<div class="container">
    <h1><?php echo $title; ?></h1>
    <table>
        <tr>
            <?php if ($view_type == 'fraud'): ?>
                <th>Fraud ID</th>
                <th>Transaction ID</th>
                <th>Reason</th>
            <?php else: ?>
                <th>Transaction ID</th>
                <th>Amount</th>
                <th>User ID</th>
            <?php endif; ?>
            <th>Timestamp</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <?php if ($view_type == 'fraud'): ?>
                <td><?php echo $row['fraud_id']; ?></td>
                <td><?php echo $row['transaction_id']; ?></td>
                <td><?php echo $row['reason']; ?></td>
            <?php else: ?>
                <td><?php echo $row['transaction_id']; ?></td>
                <td><?php echo $row['amount']; ?></td>
                <td><?php echo $row['user_id']; ?></td>
            <?php endif; ?>
            <td><?php echo $row['timestamp']; ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
