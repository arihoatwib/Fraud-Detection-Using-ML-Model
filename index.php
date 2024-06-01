<?php date_default_timezone_set("Africa/Nairobi"); ?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Online Payment Fraud Detection System</title>

    <link href="css/all.css" rel="stylesheet" type="text/css">
  
   
    <link href="css/sb-admin-2.css" rel="stylesheet">

</head>

<body>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<a class="navbar-brand" href="">Online Payment Fraud Detection System</a>
	</nav>
    <div class="container">
		<div class="row justify-content-center">
			<div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block"><img src="image/back1.jpg" height="100%" width="100%"/></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">USER LOGIN</h1>
                                    </div>
                                    <form method="POST" class="user" action="login.php">
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user" name="username" placeholder="Enter Username here..." required="required">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" name="password" placeholder="Enter Password here..." required="required">
                                        </div>
										<?php 
											session_start();
											if(isset($_SESSION['message'])){
												echo "<center><label class='text-danger'>".$_SESSION['message']."</label></center>";
												unset($_SESSION['message']);
											}
										?>
                                        <button type="submit" class="btn btn-primary btn-user btn-block" name="login">Login</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<nav class="navbar fixed-bottom navbar-dark bg-dark">
		<label style="color:#ffffff;">&copy; Copyright Online Payment Fraud Detection System</label>
		<label style="color:#ffffff;">All Rights Reserved <?php echo date("Y")?> </label>
	</nav>

    <!-- Include jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Include any custom JavaScript files -->
<script src="js/custom.js"></script>

<link href="css/all.css" rel="stylesheet" type="text/css">
<link href="css/sb-admin-2.css" rel="stylesheet">

<!-- Include jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Include any custom JavaScript files -->
<script src="js/custom.js"></script>

</body>

</html>
