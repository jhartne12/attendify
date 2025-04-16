<?php
include("DBConnect.php");

$token = $_GET["token"] ?? '';

if (!$token) {
  die("Invalid or missing token.");
}

$stmt = $conn->prepare("SELECT email, reset_token_expiry FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
  die("Invalid token.");
}

$row = $result->fetch_assoc();
if (strtotime($row["reset_token_expiry"]) < time()) {
  die("Token has expired.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $newPassword = $_POST["new_password"];
  
  if (strlen($newPassword) < 6) {
    echo "Password must be at least 6 characters.";
  } else {
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $email = $row["email"];

    // Clear token and update password
    $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE email = ?");
    $update->bind_param("ss", $hashed, $email);

    if ($update->execute()) {
      echo "Password successfully reset.";
    } else {
      echo "Error updating password.";
    }
  }
}
?>

<!-- HTML Form -->
<form method="post">
  New Password: <input type="password" name="new_password" required>
  <input type="submit" value="Reset Password">
</form>