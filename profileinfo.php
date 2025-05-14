<?php
session_start();
include("DBConnect.php");
openDB();
global $conn;

// check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$message = "";
$userData = [];
$role = $_SESSION['role'];
$email = $_SESSION['username'];

// map roles to their id columns
$role_id_columns = [
    "attendee" => "attendeeID",
    "organizer" => "organizerID",
    "admin"    => "adminID"
];

// fetch user details
$table = $role;
$id_col = $role_id_columns[$role];

$stmt = $conn->prepare("SELECT $id_col, name, email, securityQ FROM $table WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $userData = $result->fetch_assoc();
} else {
    $message = "User not found.";
}
$stmt->close();

// update user details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $name = trim($_POST["name"]);
    $password = trim($_POST["password"]);
    $securityQ = trim($_POST["securityQ"]);
    $securityA = trim($_POST["securityA"]);

    $query = "UPDATE $table SET name = ?, securityQ = ?, ";
    $params = [$name, $securityQ];
    $types = "ss";

    // handle optional fields
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $message = "Password must be at least 8 characters.";
        } else {
            $query .= "password = ?, ";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
            $types .= "s";
        }
    }

    if (!empty($securityA)) {
        $query .= "securityA = ?, ";
        $params[] = password_hash($securityA, PASSWORD_DEFAULT);
        $types .= "s";
    }

    // finalize query
    $query = rtrim($query, ", ") . " WHERE $id_col = ?";
    $params[] = $userData[$id_col];
    $types .= "i";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $message = "Profile updated successfully.";
    } else {
        $message = "Error updating profile.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>edit my profile</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profileinfo.php">Profile Info</a>
                    </li>
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
        <h2 class="text-center">Edit my profile</h2>

        <?php if (!empty($message)) echo "<div class='alert alert-info text-center'>$message</div>"; ?>

        <form method="post" class="mt-4">
            <table class="table registration-table table-bordered">
                <thead>
                    <tr>
                        <th colspan="2" class="text-center">Edit profile</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><label for="name" class="form-label">Name:</label></td>
                        <td><input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($userData['name']) ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="password" class="form-label">New Password (optional):</label></td>
                        <td><input type="password" name="password" id="password" class="form-control"></td>
                    </tr>
                    <tr>
                        <td><label for="securityQ" class="form-label">Security Question:</label></td>
                        <td><input type="text" name="securityQ" id="securityQ" class="form-control" value="<?= htmlspecialchars($userData['securityQ']) ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="securityA" class="form-label">New Security Answer (optional):</label></td>
                        <td><input type="text" name="securityA" id="securityA" class="form-control"></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-center">
                            <button type="submit" name="update" class="btn btn-success">Update Profile</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
</body>
</html>