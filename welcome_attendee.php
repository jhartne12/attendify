<?php
session_start();
include("DBConnect.php");
openDB();
global $conn;

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'attendee') {
    header('Location: index.php');
    exit();
}

$email = $_SESSION['username'];

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eventID'])) {
    $eventID = intval($_POST['eventID']);
    $check_sql = "SELECT * FROM event_attendee WHERE attendee = '$email' AND eventID = $eventID";
    $result = $conn->query($check_sql);

    if ($result->num_rows == 0) {
        $insert_sql = "INSERT INTO event_attendee (attendeeID, eventID) VALUES ('$email', $eventID)";
        $conn->query($insert_sql);
        echo "<p style='color:green;'>Successfully registered for event #$eventID</p>";
    } else {
        echo "<p style='color:orange;'>You are already registered for this event.</p>";
    }
}

// Fetch all events
$events_result = $conn->query("SELECT * FROM event");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome Attendee</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['username']; ?> (Attendee)</h2>
    <p>You have access to attendee features.</p>
    <h3>Available Events</h3>
    <table border="1" cellpadding="5">
        <tr><th>Event ID</th><th>Title</th><th>Description</th><th>Action</th></tr>
        <?php while ($event = $events_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $event['eventID']; ?></td>
                <td><?php echo $event['Name']; ?></td>
                <td><?php echo $event['description']; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="eventID" value="<?php echo $event['eventID']; ?>">
                        <input type="submit" value="Register">
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <br><a href="logout.php">Logout</a>
</body>
</html>