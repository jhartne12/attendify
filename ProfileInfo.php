<?php
session_start();
include("DBConnect.php");
openDB();
global $conn;

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

        $stmt = $conn->prepare("SELECT $id_col, name, email, securityQ, securityA FROM $table WHERE email = ?");
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
<html>
<head>
    <title>Edit User Profile</title>
</head>
<body>
    <h2>Edit User Profile</h2>

    <?php if (!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>

    <form method="post">
        <label>Select Role:</label>
        <select name="role" required>
            <option value="">--Select Role--</option>
            <option value="attendee" <?= ($role === "attendee") ? "selected" : "" ?>>Attendee</option>
            <option value="organizer" <?= ($role === "organizer") ? "selected" : "" ?>>Organizer</option>
            <option value="admin" <?= ($role === "admin") ? "selected" : "" ?>>Admin</option>
        </select><br><br>

        <label>Email:</label>
        <input type="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        <button type="submit" name="fetch">Fetch User</button>
    </form>

    <?php if (!empty($userData)) : ?>
        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($userData[$role_id_columns[$role]]) ?>">
            <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

            <p>
                <label>Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($userData['name']) ?>" required>
            </p>

            <p>
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>
            </p>

            <p>
                <label>New Password (optional):</label>
                <input type="password" name="password">
            </p>

            <p>
                <label>Security Question:</label>
                <input type="text" name="securityQ" value="<?= htmlspecialchars($userData['securityQ']) ?>" required>
            </p>

            <p>
                <label>New Security Answer (optional):</label>
                <input type="text" name="securityA">
            </p>

            <button type="submit" name="update">Update User</button>
        </form>
    <?php endif; ?>
</body>
</html>
