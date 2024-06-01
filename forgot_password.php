<?php
include('config.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $token = bin2hex(random_bytes(50)); // Generate a secure token
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token expires in 1 hour

        $updateQuery = "UPDATE users SET reset_token='$token', reset_token_expiry='$expiry' WHERE email='$email'";
        if (mysqli_query($conn, $updateQuery)) {
            $resetLink = "http://yourdomain.com/reset_password.php?token=$token";
            // Send email to user with reset link (mail function not shown here)
            // mail($email, "Password Reset", "Click this link to reset your password: $resetLink");
            echo "A password reset link has been sent to your email.";
        } else {
            echo "Error updating token.";
        }
    } else {
        echo "No user found with that email.";
    }
}
?>

<!-- HTML Form for requesting password reset -->
<form method="POST" action="forgot_password.php">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>
</form>
