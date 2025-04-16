<?php
include("DBConnect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST["email"]);

  $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // Generate token & expiration
    $token = bin2hex(random_bytes(32));
    $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
    $update->bind_param("sss", $token, $expiry, $email);
    $update->execute();

    // Simulate sending email (in production, send this link)
    $resetLink = "http://yourdomain.com/resetPassword.php?token=" . urlencode($token);
    echo "Password reset link (send via email): <a href='$resetLink'>$resetLink</a>";
  } else {
    echo "Email not found!";
  }
}
?>

<!-- HTML Form -->
<form method="post" action="">
  Enter your email to reset password: <input type="email" name="email" required>
  <input type="submit" value="Submit">
</form>