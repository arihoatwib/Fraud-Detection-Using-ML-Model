<?php
session_start();
include('config.php');

if (!isset($_SESSION['login_user']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id']) && isset($_POST['role_id'])) {
    $user_id = $_POST['user_id'];
    $role_id = $_POST['role_id'];
    
    $query = "UPDATE users SET role_id='$role_id' WHERE id='$user_id'";
    if (mysqli_query($conn, $query)) {
        echo "User role updated.";
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}

$result = mysqli_query($conn, "SELECT users.id, users.username, roles.role_name FROM users JOIN roles ON users.role_id = roles.id");

echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Role</th><th>Action</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>{$row['id']}</td><td>{$row['username']}</td><td>{$row['role_name']}</td>";
    echo "<td><form method='POST' action='user_management.php'><input type='hidden' name='user_id' value='{$row['id']}'><select name='role_id'>";
    
    $rolesResult = mysqli_query($conn, "SELECT * FROM roles");
    while ($role = mysqli_fetch_assoc($rolesResult)) {
        $selected = ($role['id'] == $row['role_id']) ? "selected" : "";
        echo "<option value='{$role['id']}' $selected>{$role['role_name']}</option>";
    }
    
    echo "</select><button type='submit'>Update Role</button></form></td></tr>";
}
echo "</table>";
?>

<a href="admin.php">Back to Admin Dashboard</a>
<a href="logout.php">Logout</a>
