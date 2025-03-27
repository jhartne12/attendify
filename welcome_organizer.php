<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'organizer') {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome Organizer</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['username']; ?> (Organizer)</h2>
    <p>You have access to organizer features.</p>
    <a href="logout.php">Logout</a>
</body>
</html>