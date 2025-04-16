<?php
session_start();
include("DBConnect.php");
openDB();
global $conn;

if (!isset($_SESSION['email']) || $_SESSION['role'] != 'attendee') {
    exit("Unauthorized access.");
}

$email = $_SESSION['email'];

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);
    $check_sql = "SELECT * FROM event_registrations WHERE attendee_email = '$email' AND event_id = $event_id";
    $result = $conn->query($check_sql);

    if ($result->num_rows == 0) {
        $insert_sql = "INSERT INTO event_registrations (attendee_email, event_id) VALUES ('$email', $event_id)";
        $conn->query($insert_sql);
        echo "<p style='color:green;'>Successfully registered for event #$event_id</p>";
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
    <h2>Welcome, <?php echo $_SESSION['email']; ?> (Attendee)</h2>
    <p>You have access to attendee features.</p>
    <h3>Available Events</h3>
    <table border="1" cellpadding="5">
        <tr><th>Event ID</th><th>Title</th><th>Description</th><th>Action</th></tr>
        <?php while ($event = $events_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $event['event_id']; ?></td>
                <td><?php echo $event['title']; ?></td>
                <td><?php echo $event['description']; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                        <input type="submit" value="Register">
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <br><a href="logout.php">Logout</a>
</body>
</html>
