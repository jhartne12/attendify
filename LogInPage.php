<?php
session_start();
include('DBConnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; // Using plain text, will verify later
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Fetch user details from DB
    $query = "SELECT * FROM users WHERE username='$username' AND role='$role'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password using password_verify
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            // Redirect based on role
            switch ($role) {
                case 'attendee':
                    header('Location: welcome_attendee.php');
                    exit();
                case 'organizer':
                    header('Location: welcome_organizer.php');
                    exit();
                case 'admin':
                    header('Location: welcome_admin.php');
                    exit();
            }
        } else {
            $error = "Invalid username, password, or role!";
        }
    } else {
        $error = "Invalid username, password, or role!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) { echo "<p>" . htmlspecialchars($error) . "</p>"; } ?>
    <form method="POST" action="index.php">
        <label>Username:</label>
        <input type="text" name="username" required><br><br>
        
        <label>Password:</label>
        <input type="password" name="password" required><br><br>
        
        <label>Select Role:</label>
        <select name="role" required>
            <option value="attendee">Attendee</option>
            <option value="organizer">Organizer</option>
            <option value="admin">Admin</option>
        </select><br><br>
        
        <input type="submit" value="Login">
    </form>
    
    <p><a href="forgotPassword.php">Forgot Password?</a></p>
</body>
</html>
