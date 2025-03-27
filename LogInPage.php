<?php
session_start();
include('DBConnect.php');


    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = md5($_POST['password']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);

        $query = "SELECT * FROM users WHERE username='$username' AND password='$password' AND role='$role'";
        $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        // Redirect based on role
        switch ($role) {
            case 'attendee':
                header('Location: welcome_attendee.php');
                break;
            case 'organizer':
                header('Location: welcome_organizer.php');
                break;
            case 'admin':
                header('Location: welcome_admin.php');
                break;
        }
    } else {
        $error = "Invalid username, password, or role!";
    }
}
?>

<!DOCTYPE html>
<html>
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    <form method="POST" action="index.php">
        <label>Username:</label>
        <input type="text" name="username" required><br><br>
        <label>Password:</label>
        <input type="password" name="password" required><br><br>
        <input type="submit" value="Login">
        <label>Select Role:</label>
        <select name="role" required>
            <option value="attendee">Attendee</option>
            <option value="organizer">Organizer</option>
            <option value="admin">Admin</option>
        </select><br><br>
        
    <p><a href="forgotPassword.php">Forgot Password?</a></p>
    </form>
</body>
</html>

