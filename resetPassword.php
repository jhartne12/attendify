<?php
include('DBConnect.php');

if (isset($_GET['email'])) {
    $email = $_GET['email'];

    $query = "SELECT * FROM users WHERE reset_token='$email' AND token_expiry > NOW()";
    $result = mysqli_query( $query);

    if (mysqli_num_rows($result) == 1) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = md5($_POST['new_password']);
            $update_query = "UPDATE users SET password='$new_password', reset_token=NULL, token_expiry=NULL WHERE reset_token='$token'";
            
            if (mysqli_query( $update_query)) {
                echo "Password reset successfully! <a href='index.php'>Login</a>";
                exit();
            } else {
                echo "Error resetting password!";
            }
        }
    } else {
        echo "Invalid or expired token!";
        exit();
    }
} else {
    echo "Invalid request!";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Your Password</h2>
    <form method="POST" action="">
        <label>New Password:</label>
        <input type="password" name="new_password" required><br><br>
        <input type="submit" value="Reset Password">
    </form>
</body>
</html>