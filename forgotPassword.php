<?php
session_start();
include("DBConnect.php");

if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    header("Location: welcome_" . $_SESSION['role'] . ".php");
    exit();
}

openDB();
global $conn;

$step = 1;
$error = "";
$securityQ = "";
$email = $role = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $role = trim($_POST["role"]);

    $allowed_roles = ["attendee", "organizer", "admin"];
    if (!in_array($role, $allowed_roles)) {
        $error = "Invalid role selected.";
    } else {
        if (isset($_POST["step"]) && $_POST["step"] == "1") {
            // Step 1: Get security question
            $stmt = $conn->prepare("SELECT securityQ FROM $role WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($securityQ);
            if ($stmt->fetch()) {
                $step = 2;
            } else {
                $error = "No user found with that email.";
            }
            $stmt->close();
        }

        elseif (isset($_POST["step"]) && $_POST["step"] == "2") {
            // Step 2: Check security answer and reset password
            $securityA = trim($_POST["securityA"]);
            $newPassword = $_POST["newPassword"];


            $stmt = $conn->prepare("SELECT securityA FROM $role WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($storedA);
            if ($stmt->fetch()) {
                if (strtolower(trim($storedA)) === strtolower(trim($securityA))) {
                    $stmt->close();



                    if (strlen($newPassword) < 8) {
                        $error = "Password must be at least 8 characters long.";
                    } else {
                        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                        $update = $conn->prepare("UPDATE $role SET password = ? WHERE email = ?");
                        $update->bind_param("ss", $hashed, $email);
                        if ($update->execute()) {
                            echo "<p style='text-align:center; color:green;'>Password reset successful. <a href='LogInPage.php'>Login here</a>.</p>";
                            closeDB();
                            exit();
                        } else {
                            $error = "Failed to update password.";
                        }
                        $update->close();
                    }


                } else {
                    $error = "Incorrect security answer.";
                }
            } else {
                $error = "User not found.";
            }
            $stmt->close();
        }
    }
}

closeDB();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <title>Forgot Password - Attendify</title>
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
    
    <h2 style="text-align:center;">Forgot Password</h2>
    <?php if ($error): ?>
        <p style="text-align:center; color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($step === 1): ?>
        <form method="POST" style="width:300px; margin:auto;">
            <input type="hidden" name="step" value="1">
            <label>Email:</label><br>
            <input type="email" name="email" required><br><br>

            <label>Role:</label><br>
            <select name="role" required>
                <option value="">--Select--</option>
                <option value="attendee">Attendee</option>
                <option value="organizer">Organizer</option>
                <option value="admin">Admin</option>
            </select><br><br>

            <input type="submit" value="Next">
        </form>

    <?php elseif ($step === 2): ?>
        <form method="POST" style="width:300px; margin:auto;">
            <input type="hidden" name="step" value="2">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">

            <label>Security Question:</label><br>
            <input type="text" value="<?php echo htmlspecialchars($securityQ); ?>" disabled><br><br>

            <label>Your Answer:</label><br>
            <input type="text" name="securityA" required><br><br>

            <label>New Password:</label><br>

            <input type="password" name="newPassword" minlength="8" required><br>
            <small>Password must be at least 8 characters long.</small><br><br>

            <input type="submit" value="Reset Password">
        </form>
    <?php endif; ?>
</body>
</html>

