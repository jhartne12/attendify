<?php
require_once 'DBConnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $newPassword = htmlspecialchars(trim($_POST['newPassword']));
    $role = htmlspecialchars(trim($_POST['role']));

    if (empty($email) || empty($newPassword) || empty($role)) {
        echo "All fields are required.";
        exit();
    }

    $conn = openDB();

    if ($role == "attendee") {
        $table = "attendee";
    } elseif ($role == "organizer") {
        $table = "organizer";
    } elseif ($role == "admin") {
        $table = "admin";
    } else {
        echo "Invalid role selected.";
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password
    $stmt = $conn->prepare("UPDATE $table SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);

    if ($stmt->execute()) {
        echo "Password reset successfully! <a href='LogInPage.php'>Login here</a>.";
    } else {
        echo "Error resetting password: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body>
    <h2 style="text-align:center;">Reset Password</h2>
    <form method="POST" action="resetPassword.php" style="width:300px;margin:auto;">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="newPassword">New Password:</label><br>
        <input type="password" id="newPassword" name="newPassword" required><br><br>

        <label for="role">Select Role:</label><br>
        <select id="role" name="role" required>
            <option value="">--Select--</option>
            <option value="attendee">Attendee</option>
            <option value="organizer">Organizer</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <input type="submit" value="Reset Password">
    </form>
</body>
</html>
