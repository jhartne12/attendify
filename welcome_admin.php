<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome Admin</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['username']; ?> (Admin)</h2>
    <p>You have access to admin features.</p>
    <a href="logout.php">Logout</a>
</body>
</html>