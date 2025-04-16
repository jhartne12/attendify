<?php
session_start();
include("DBConnect.php");

openDB();
global $conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $tables = ["attendee", "organizer", "admin"];
    $found = false;

    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT email FROM $table WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            // Simulate a reset link with role info in the URL
            $reset_link = "resetPassword.php?email=" . urlencode($email) . "&role=" . $table;
            echo "Password reset link: <a href='$reset_link'>$reset_link</a>";
            $found = true;
            break;
        }
    }

    if (!$found) {
        echo "Email not found in any user table.";
    }

    closeDB();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password - Attendify</title>
</head>
<body>
  <h2>Forgot Password</h2>
  <form method="post">
    <label>Email:</label>
    <input type="email" name="email" required>
    <input type="submit" value="Send Reset Link">
  </form>
</body>
</html>
