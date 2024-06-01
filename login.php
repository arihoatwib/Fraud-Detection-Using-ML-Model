<?php
include('config.php');
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // reCAPTCHA validation
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $secretKey = "YOUR_SECRET_KEY";
    $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}");
    $responseData = json_decode($verifyResponse);
    if ($responseData->success) {
        $query = "SELECT * FROM users WHERE username='$username'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if (password_verify($password, $row['password'])) {
                $_SESSION['login_user'] = $username;
                header("location: dashboard.php");
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with this username.";
        }
    } else {
        echo "reCAPTCHA verification failed.";
    }
}
?>

<!-- HTML Form for login -->
<form method="POST" action="login.php">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>