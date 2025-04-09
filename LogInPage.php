<?php
session_start();
include('DBConnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uname = $_POST['uname'];
    $passwd = $_POST['passwd'];
    $role = $_POST['role'];
    switch ($role) {
        case 'admin':
            $table = 'admin';
            $idField = 'adminID';
            break;
        case 'attendee':
            $table = 'attendee';
            $idField = 'attendeeID';
            break;
        case 'organizer':
            $table = 'organizer';
            $idField = 'organizerID';
            break;
        default:
            $error = "Invalid role.";
            break;
    }

    if (isset($table)) {
        $sql = "SELECT $idField, Name, password FROM $table WHERE username = ?";
        $result = loginDB($sql, $uname, $passwd);

        if (gettype($result) == "object") {
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $userID = $row[$idField];
                $name = $row['Name'];
                $role = $table;

                // Start session and save user data
                $_SESSION['userID'] = $userID;
                $_SESSION['name'] = $name;
                $_SESSION['role'] = $role;

                // Redirect based on role
                switch ($role) {
                    case 'admin':
                        header('Location: welcome_admin.php');
                        break;
                    case 'attendee':
                        header('Location: welcome_attendee.php');
                        break;
                    case 'organizer':
                        header('Location: welcome_organizer.php');
                        break;
                    default:
                        header('Location: welcome.php');
                        break;
                }
                exit;
            } else {
                $error = "Invalid username or password!";
            }
        }
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
<?php if (isset($error)) {
    echo "<p style='color:red;'>$error</p>";
} ?>
        <form method="POST" action="">
            <label>Username:</label>
            <input type="text" name="uname" required><br><br>
            <label>Password:</label>
            <input type="password" name="passwd" required><br><br>
            <label>Select Role:</label>
            <select name="role" required>
                <option value="attendee">Attendee</option>
                <option value="organizer">Organizer</option>
                <option value="admin">Admin</option>
            </select><br><br>
            <input type="submit" value="Login">
        </form>
        <p><a href="registration.php ">Not A Member? Register Here!</a></p>
        <p><a href="forgotPassword.php">Forgot Password?</a></p>
    </body>
</html>