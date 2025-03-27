<?php
include('DBConnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $query = "SELECT * FROM users WHERE username='$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $token = bin2hex(random_bytes(50)); // Generate token
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour

        $update_query = "UPDATE users SET reset_token='$token', token_expiry='$expiry' WHERE username='$email'";
        mysqli_query($conn, $update_query);

        $reset_link = "http://localhost/login_system/reset_password.php?token=$token";
        
        // Send email (Replace with your mail function)
        $to = $email;
        $subject = "Password Reset";
        $message = "Click on this link to reset your password: $reset_link";
        $headers = "From: noreply@yourdomain.com";

        if (mail($to, $subject, $message, $headers)) {
            echo "A password reset link has been sent to your email!";
        } else {
            echo "Failed to send reset link. Check mail configuration.";
        }
    } else {
        echo "No user found with that email!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <form method="POST" action="forgotPassword.php">
        <label>Enter your email:</label>
        <input type="email" name="email" required><br><br>
        <input type="submit" value="Send Reset Link">
    </form>
</body>
</html>