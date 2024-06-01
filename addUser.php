<?php
	require_once'class.php';
	if(ISSET($_POST['confirm'])){
		$db=new db_class();
		$username=$_POST['username'];
		$password=$_POST['password'];
		$email=$_POST['email'];
		$db->add_user($username,$password,$email);
		echo"<script>alert('User added successfully')</script>";
		echo"<script>window.location='user.php'</script>";
	}
?>