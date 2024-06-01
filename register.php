<?php
include('config.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $role_id = 3; // Default role: User

    // reCAPTCHA validation
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $secretKey = "YOUR_SECRET_KEY";
    $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}");
    $responseData = json_decode($verifyResponse);
    if ($responseData->success) {
        $query = "INSERT INTO users (username, password, email, role_id) VALUES ('$username', '$password', '$email', '$role_id')";
        if (mysqli_query($conn, $query)) {
            echo "Registration successful.";
        } else {
            echo "Error: " . $query . "<br>" . mysqli_error($conn);
        }
    } else {
        echo "reCAPTCHA verification failed.";
    }
}
?>

<!-- HTML Form for registration -->
<form method="POST" action="register.php">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="email" name="email" placeholder="Email" required>
    <button type="submit">Register</button>
</form>
