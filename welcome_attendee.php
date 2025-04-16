<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'attendee') {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome Attendee</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['username']; ?> (Attendee)</h2>
    <p>You have access to attendee features.</p>
    <a href="index.php">Signing up for an event? Click here!</a>
    <br>
    <a href="logout.php">Logout</a>
</body>
</html>