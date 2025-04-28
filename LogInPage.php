<?php
session_start();
require_once 'DBConnect.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $role = htmlspecialchars(trim($_POST['role']));

    if (empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        $conn = openDB();

        // Determine the correct table and redirect page
        if ($role == "attendee") {
            $table = "attendee";
            $redirect = "welcome_attendee.php";
        } elseif ($role == "organizer") {
            $table = "organizer";
            $redirect = "welcome_organizer.php";
        } elseif ($role == "admin") {
            $table = "admin";
            $redirect = "welcome_admin.php";
        } else {
            $error = "Invalid role selected.";
        }

        if (empty($error)) {
            $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify the hashed password
                if (password_verify($password, $user['password'])) {
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $role;
                    header("Location: $redirect");
                    exit();
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "No user found with that email.";
            }

            $stmt->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2 style="text-align:left;">Login</h2>
    <form method="POST" action="LogInPage.php" style="margin:auto;">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label for="role">Select Role:</label><br>
        <select id="role" name="role" required>
            <option value="">--Select--</option>
            <option value="attendee">Attendee</option>
            <option value="organizer">Organizer</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <input type="submit" value="Login"><br><br>

        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    </form>
  <p><a href="registration.php">Not A Member? Register Here!</a></p>
  <p><a href="forgotPassword.php">Forgot Password?</a></p>
</body>
</html>
