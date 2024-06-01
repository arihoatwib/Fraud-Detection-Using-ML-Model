<?php
include '../includes/header.php';
include '../includes/database.php';

$query = "SELECT * FROM transactions";
$result = mysqli_query($conn, $query);
?>

<div class="container">
    <h1>All Transactions</h1>
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
include '../includes/footer.php';
?>
