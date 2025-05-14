<?php
session_start();
include("DBConnect.php");

if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    header("Location: welcome_" . $_SESSION['role'] . ".php");
    exit();
}

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
  <title>Password Reset - Attendify</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <nav class="navbar navbar-expand-sm navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Attendify</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mynavbar">
                <ul class="navbar-nav me-auto">
                </ul>
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="welcome_<?php echo $_SESSION['role']; ?>.php" class="btn btn-primary">Welcome <?php echo htmlspecialchars($_SESSION['role']); ?>, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                    <a href="logout.php" class="btn btn-danger ms-2">Logout</a>
                <?php else: ?>
                    <a href="LogInPage.php" class="btn btn-primary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <h2>Reset Your Password</h2>
    <form method="post">
        <label>New Password:</label>
        <input type="password" name="new_password" required>
        <input type="submit" value="Reset Password">
    </form>
</body>
</html>
