<?php
include('DBConnect.php');

if (isset($_GET['token'])) {
    // Ensure you are using the database connection for escaping the token.
    $token = mysqli_real_escape_string($conn, $_GET['token']);

    // Prepare the SQL query to prevent SQL injection
    $query = "SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 's', $token); // Bind the token as a string
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    
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