<?php
session_start();
if (!isset($_SESSION['login_user'])) {
    header("Location: login.php");
    exit;
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reporting and Compliance Dashboard</title>
    <link href="css/all.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1>Welcome, <?php echo $_SESSION['login_user']; ?></h1>
        <a href="logout.php">Logout</a>

        <?php if ($_SESSION['role_id'] == 1): ?>
            <a href="admin.php">Admin Dashboard</a>
        <?php endif; ?>

        <h1>Reporting and Compliance Dashboard</h1>
        <ul>
            <li><a href="log_transaction.php">Log Transaction</a></li>
            <li><a href="log_fraud.php">Log Fraud</a></li>
            <li><a href="view_transactions.php">View Transactions</a></li>
            <li><a href="view_fraud.php">View Fraud Cases</a></li>
            <li><a href="generate_report.php">Generate Report</a></li>
            <li><a href="report_compliance.php">Compliance Report</a></li>
        </ul>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Include jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Include any custom JavaScript files -->
    <script src="js/custom.js"></script>
</body>

</html>
