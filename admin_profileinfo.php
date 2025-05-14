<?php
session_start();
include("DBConnect.php");
openDB();
global $conn;

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

$message = "";
$userData = [];
$role = "";

$role_id_columns = [
    "attendee" => "attendeeID",
    "organizer" => "organizerID",
    "admin"    => "adminID"
];

// Fetch user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["fetch"])) {
    $email = trim($_POST["email"]);
    $role = trim($_POST["role"]);

    if (!array_key_exists($role, $role_id_columns)) {
        $message = "Invalid role selected.";
    } else {
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
    }
}

// Update user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $id = $_POST["id"];
    $role = trim($_POST["role"]);
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $securityQ = trim($_POST["securityQ"]);
    $securityA = trim($_POST["securityA"]);

    if (!array_key_exists($role, $role_id_columns)) {
        $message = "Invalid role selected.";
    } else {
        $table = $role;
        $id_col = $role_id_columns[$role];

        $query = "UPDATE $table SET name = ?, email = ?, securityQ = ?, ";
        $params = [$name, $email, $securityQ];
        $types = "sss";

        // Optional fields
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

        // Remove last comma and add WHERE clause
        $query = rtrim($query, ", ") . " WHERE $id_col = ?";
        $params[] = $id;
        $types .= "i";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $message = "User updated successfully.";
        } else {
            $message = "Error updating user.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin - Edit User Profile</title>
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
                        <a class="nav-link" href="admin_register.php">Create User</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_events.php">Manage Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_profileinfo.php">Edit Profile</a>
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
        <h2 class="text-center">Admin - Edit User Profile</h2>

        <?php if (!empty($message)) echo "<div class='alert alert-danger'>$message</div>"; ?>

        <form method="post" class="mt-4">
            <table class="table registration-table table-bordered">
                <thead>
                    <tr>
                        <th colspan="2" class="text-center">Fetch User</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><label for="role" class="form-label">Select Role:</label></td>
                        <td>
                            <select name="role" id="role" class="form-select" required>
                                <option value="">--Select Role--</option>
                                <option value="attendee" <?= ($role === "attendee") ? "Selected" : "" ?>>Attendee</option>
                                <option value="organizer" <?= ($role === "organizer") ? "Selected" : "" ?>>Organizer</option>
                                <option value="admin" <?= ($role === "admin") ? "Selected" : "" ?>>Admin</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="email" class="form-label">Email:</label></td>
                        <td><input type="email" name="email" id="email" class="form-control" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-center">
                            <button type="submit" name="fetch" class="btn btn-primary">Fetch User</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>

        <?php if (!empty($userData)) : ?>
            <form method="post" class="mt-4">
                <table class="table registration-table table-bordered">
                    <thead>
                        <tr>
                            <th colspan="2" class="text-center">Edit User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($userData[$role_id_columns[$role]]) ?>">
                        <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

                        <tr>
                            <td><label for="name" class="form-label">Name:</label></td>
                            <td><input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($userData['name']) ?>" required></td>
                        </tr>
                        <tr>
                            <td><label for="email" class="form-label">Email:</label></td>
                            <td><input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($userData['email']) ?>" required></td>
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
                                <button type="submit" name="update" class="btn btn-success">Update User</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>