<?php
include('config.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($action == 'register') {
        $email = $_POST['email'];
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $role_id = 3; // Default role: User

        // reCAPTCHA validation
        $recaptchaResponse = $_POST['g-recaptcha-response'];
        $secretKey = "YOUR_SECRET_KEY";
        $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}");
        $responseData = json_decode($verifyResponse);
        if ($responseData->success) {
            $query = "INSERT INTO users (username, password, email, role_id) VALUES ('$username', '$hashed_password', '$email', '$role_id')";
            if (mysqli_query($conn, $query)) {
                echo "Registration successful.";
            } else {
                echo "Error: " . $query . "<br>" . mysqli_error($conn);
            }
        } else {
            echo "reCAPTCHA verification failed.";
        }
    } elseif ($action == 'login') {
        $recaptchaResponse = $_POST['g-recaptcha-response'];
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
}
?>

<!-- HTML Form for login and registration -->
<form method="POST" action="admin.php">
    <input type="hidden" name="action" value="login">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

<form method="POST" action="admin.php">
    <input type="hidden" name="action" value="register">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="email" name="email" placeholder="Email" required>
    <button type="submit">Register</button>
</form>
