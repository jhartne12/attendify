<?php
session_start();
include("DBConnect.php");

if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    header("Location: welcome_" . $_SESSION['role'] . ".php");
    exit();
}

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
            $_SESSION["username"] = $row["email"];
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
<html lang="en">
<head>
  <title>Login - Attendify</title>
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

    <div class="container mt-5">
        <h2 class="text-center">Login to Attendify</h2>
        <form method="post" action="">
            <table class="table registration-table table-bordered w-50 mx-auto">
                <tbody>
                    <tr>
                        <td><label for="email" class="form-label">Email:</label></td>
                        <td><input type="email" class="form-control" id="email" name="email" required></td>
                    </tr>
                    <tr>
                        <td><label for="password" class="form-label">Password:</label></td>
                        <td><input type="password" class="form-control" id="password" name="password" required></td>
                    </tr>
                    <tr>
                        <td><label for="role" class="form-label">Login as:</label></td>
                        <td>
                            <select class="form-select" id="role" name="role" required>
                                <option value="" disabled selected>-- Select Role --</option>
                                <option value="attendee">Attendee</option>
                                <option value="organizer">Organizer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
                <br>
            <div class="text-center">
                <a href="registration.php" class="btn btn-success ms-2">Not a Member? Register</a>
                <br>
                <br>
                <a href="forgotPassword.php" class="btn btn-warning ms-2">Forgot Password?</a>
            </div>
        </form>
    </div>
</body>
</html>