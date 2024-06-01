<?php
session_start();
include('config.php');

if (!isset($_SESSION['login_user']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['role_name'])) {
    $role_name = $_POST['role_name'];
    
    $query = "INSERT INTO roles (role_name) VALUES ('$role_name')";
    if (mysqli_query($conn, $query)) {
        echo "New role added.";
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}

$result = mysqli_query($conn, "SELECT * FROM roles");

echo "<table border='1'><tr><th>ID</th><th>Role Name</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>{$row['id']}</td><td>{$row['role_name']}</td></tr>";
}
echo "</table>";
?>

<form method="POST" action="role_management.php">
    <input type="text" name="role_name" placeholder="Role Name" required>
    <button type="submit">Add Role</button>
</form>

<a href="admin.php">Back to Admin Dashboard</a>
<a href="logout.php">Logout</a>
