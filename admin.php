<?php
session_start();
if (!isset($_SESSION['login_user']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

echo "Admin Dashboard";

echo '<a href="user_management.php">Manage Users</a>';
echo '<a href="role_management.php">Manage Roles</a>';
?>

<a href="logout.php">Logout</a>
