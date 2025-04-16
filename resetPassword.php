<?php
session_start();
include("DBConnect.php");

openDB();
global $conn;

// Validate that role and email are provided
if (!isset($_GET["email"]) || !isset($_GET["role"])) {
    die("Invalid password reset request.");
}

$email = trim($_GET["email"]);
$role = trim($_GET["role"]);
$allowed_roles = ["attendee", "organizer", "admin"];

if (!in_array($role, $allowed_roles)) {
    die("Invalid role specified.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST["new_password"];

    if (strlen($new_password) < 6) {
        echo "Password must be at least 6 characters long.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE $role SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed, $email);

        if ($stmt->execute()) {
            echo "Password reset successfully! You can now <a href='LogInPage.php'>Log in</a>.";
        } else {
            echo "Error resetting password. Please try again.";
        }
    }
}

closeDB();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reset Password - Attendify</title>
</head>
<body>
  <h2>Reset Your Password</h2>
  <form method="post">
    <label>New Password:</label>
    <input type="password" name="new_password" required>
    <input type="submit" value="Reset Password">
  </form>
</body>
</html>
