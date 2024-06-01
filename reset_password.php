<?php
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $query = "SELECT * FROM users WHERE reset_token='$token' AND reset_token_expiry > NOW()";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $user_id = $row['id'];
        $updateQuery = "UPDATE users SET password='$new_password', reset_token=NULL, reset_token_expiry=NULL WHERE id='$user_id'";
        if (mysqli_query($conn, $updateQuery)) {
            echo "Your password has been reset successfully.";
        } else {
            echo "Error resetting password.";
        }
    } else {
        echo "Invalid or expired token.";
    }
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];
?>
    <!-- HTML Form for resetting password -->
    <form method="POST" action="reset_password.php">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <input type="password" name="password" placeholder="Enter new password" required>
        <button type="submit">Reset Password</button>
    </form>
<?php
} else {
    echo "No token provided.";
}
?>
