<?php
    date_default_timezone_set("Africa/Nairobi");
    require_once 'session.php';
    require_once 'class.php';
    require 'secure_api.php'; // Include the secure API function

    // Establish a new database connection
    $db = new db_class();
    include('config.php');
    session_start();

    // Check if user is authenticated
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Check user roles and permissions
    $user_role = $_SESSION['user_role'];
    if (!in_array($user_role, ['Admin', 'Fraud Analyst', 'User'])) {
        echo "Access denied.";
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve transaction details
        $transaction_id = $_POST['transaction_id'];

        // Fetch transaction details from the database based on ID
        $query = "SELECT * FROM transactions WHERE id = $transaction_id";
        $result = mysqli_query($conn, $query);

        // Check if transaction is found
        if (mysqli_num_rows($result) == 1) {
            $transactionDetails = mysqli_fetch_assoc($result);

            // Perform fraud detection
            $fraudResponse = callSecureApi('http://ml-model-api/check_fraud', $transactionDetails);

            if ($fraudResponse['is_fraud']) {
                // Log fraudulent transaction
                $logQuery = "INSERT INTO fraud_logs (details) VALUES ('" . json_encode($transactionDetails) . "')";
                mysqli_query($conn, $logQuery);
                echo "Transaction flagged as fraudulent.";
            } else {
                // Perform the requested action (approve, reject, escalate)
                $action = $_POST['action'];

                // Perform action based on the submitted action
                switch ($action) {
                    case 'approve':
                        // Perform approval logic here
                        echo "Transaction approved.";
                        break;
                    case 'reject':
                        // Perform rejection logic here
                        echo "Transaction rejected.";
                        break;
                    case 'escalate':
                        // Perform escalation logic here
                        echo "Transaction escalated.";
                        break;
                    default:
                        echo "Invalid action.";
                }
            }
        } else {
            echo "Transaction not found.";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
        }
    </style>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Fraud Detection System</title>
    <link href="fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="css/dataTables.bootstrap4.css" rel="stylesheet">
    <link href="css/select2.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
                <div class="sidebar-brand-text mx-3">ADMIN PANEL</div>
            </a>
            <li class="nav-item">
                <a class="nav-link" href="home.php">
                    <i class="fas fa-fw fa-home"></i>
                    <span>Home</span></a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="payment.php">
                    <i class="fas fa-fw fas fa-coins"></i>
                    <span>Payments</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="user.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Users</span></a>
            </li>
        </ul>
        <!-- End of Sidebar -->

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $db->user_acc($_SESSION['user_id'])?></span>
                                <img class="img-profile rounded-circle" src="image/admin_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Payment List</h1>
                    </div>
                    <div class="row">
                        <button class="ml-3 mb-3 btn btn-lg btn-primary" href="#" data-toggle="modal" data-target="#addModal"><span class="fa fa-plus"></span> New Payment</button>
                    </div>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Transaction ID</th>
                                            <th>Payee</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $tbl_payment = $db->conn->query("SELECT * FROM transactions");
                                            $i = 1;
                                            while ($fetch = $tbl_payment->fetch_array()) {
                                        ?>
                                            <tr>
                                                <td><?php echo $i++?></td>
                                                <td><?php echo $fetch['transaction_id']?></td>
                                                <td><?php echo $fetch['payee']?></td>
                                                <td><?php echo " \u{0024}".number_format($fetch['amount'], 2)?></td>
                                                <td><?php echo $fetch['status'] == 1 ? 'Completed' : 'Pending' ?></td>
                                            </tr>
                                        <?php
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="stocky-footer">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Fraud Detection System <?php echo date("Y")?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <div class="modal fade" id="addModal" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="process_payment.php">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">Payment Form</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-xl-5 col-md-5">
                                <label>Transaction ID</label>
                                <br />
                                <select name="transaction_id" class="transaction_id" id="transaction_id" required="required" style="width:100%;">
                                    <option value=""></option>
                                    <?php
                                        $tbl_transactions = $db->conn->query("SELECT * FROM transactions WHERE status = 0");
                                        while ($fetch = $tbl_transactions->fetch_array()) {
                                    ?>
                                        <option value="<?php echo $fetch['id']?>"><?php echo $fetch['transaction_id']?></option>
                                    <?php
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div id="formField"></div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="save" class="btn btn-primary">Save</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">System Information</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Are you sure you want to logout?</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-danger" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.bundle.js"></script>
    <script src="js/jquery.easing.js"></script>
    <script src="js/select2.js"></script>
    <script src="js/jquery.dataTables.js"></script>
    <script src="js/dataTables.bootstrap4.js"></script>
    <script src="js/sb-admin-2.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable();
            $('.transaction_id').select2({
                placeholder: 'Select an option'
            });
            $('#transaction_id').on('change', function() {
                if ($('#transaction_id').val() == "") {
                    $('#formField').empty();
                } else {
                    $('#formField').empty();
                    $('#formField').load("get_field.php?transaction_id=" + $(this).val());
                }
            });
        });
    </script>
</body>
</html>
