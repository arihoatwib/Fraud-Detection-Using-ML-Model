<?php
include '../includes/header.php';
include '../includes/database.php';

$query = "SELECT * FROM frauds";
$result = mysqli_query($conn, $query);
?>

<div class="container">
    <h1>All Fraud Cases</h1>
    <table>
        <tr>
            <th>Fraud ID</th>
            <th>Transaction ID</th>
            <th>Reason</th>
            <th>Timestamp</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['fraud_id']; ?></td>
            <td><?php echo $row['transaction_id']; ?></td>
            <td><?php echo $row['reason']; ?></td>
            <td><?php echo $row['timestamp']; ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

<?php
include '../includes/footer.php';
?>
