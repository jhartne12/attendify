<?php
session_start();
include("DBConnect.php");

openDB();
global $conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    $allowed_roles = ["attendee", "organizer", "admin"];

    if (!in_array($role, $allowed_roles)) {
        die("Invalid role selected.");
    }

    $stmt = $conn->prepare("SELECT * FROM $role WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row["password"])) {
            $_SESSION["username"] = $row["email"];  // Matches your welcome pages
            $_SESSION["role"] = $role;

            header("Location: welcome_" . $role . ".php");
            exit();
        } else {
            echo "Invalid email or password.";
        }
    } else {
        echo "No user found for selected role.";
    }

    closeDB();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login - Attendify</title>
</head>
<body>
  <h2>Login to Attendify</h2>
  <form method="post" action="">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Login as:</label><br>
    <select name="role" required>
      <option value="">-- Select Role --</option>
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
