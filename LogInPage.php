<?php
session_start();
include("DBConnect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST["email"]);
  $password = $_POST["password"];

  // Use prepared statement to avoid SQL injection
  $stmt = $conn->prepare("SELECT email, password, role FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // Use password_verify if password is hashed
    if (password_verify($password, $row["password"])) {
      $_SESSION["email"] = $row["email"];
      $_SESSION["role"] = $row["role"];

      switch ($row["role"]) {
        case "admin":
          header("Location: welcome_admin.php");
          break;
        case "organizer":
          header("Location: welcome_organizer.php");
          break;
        case "attendee":
          header("Location: welcome_attendee.php");
          break;
      }
      exit();
    } else {
      echo "Invalid email or password!";
    }
  } else {
    echo "Invalid email or password!";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
</head>
<body>
  <h2>Login</h2>
  <form method="post" action="">
    Email: <input type="text" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Login">
  </form>
  <p><a href="registration.php ">Not A Member? Register Here!</a></p>
  <p><a href="forgotPassword.php">Forgot Password?</a></p>
</body>

</html>
