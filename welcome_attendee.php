<?php
session_start();
include("DBConnect.php");
openDB();
global $conn;

// Verify session
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'attendee') {
    header('Location: index.php');
    exit();
}

$email = $_SESSION['username'];

// fetch attendeeID based on the logged-in email
$attendee_stmt = $conn->prepare("SELECT attendeeID FROM attendee WHERE email = ?");
$attendee_stmt->bind_param("s", $email);
$attendee_stmt->execute();
$attendee_result = $attendee_stmt->get_result();

if ($attendee_result->num_rows == 0) {
    echo "<p style='color:red;'>Attendee not found.</p>";
    exit();
}

$attendee_row = $attendee_result->fetch_assoc();
$attendeeID = $attendee_row['attendeeID'];

$attendee_stmt->close();

// handle event unregistration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eventID'])) {
    $event_id = intval($_POST['eventID']);

    // Remove the attendee from the event using the attendeeID
    $delete_stmt = $conn->prepare("DELETE FROM event_attendee WHERE attendeeID = ? AND eventID = ?");
    $delete_stmt->bind_param("ii", $attendeeID, $event_id);

    if ($delete_stmt->execute()) {
        echo "<p style='color:green;'>Successfully unregistered from event #$event_id</p>";
    } else {
        echo "<p style='color:red;'>Error unregistering: " . htmlspecialchars($delete_stmt->error) . "</p>";
    }

    $delete_stmt->close();
}

// fetch events the attendee has registered for
$events_stmt = $conn->prepare("SELECT event.eventID, event.Name AS eventName, event.date, event.description, event.categoryID, organizer.Name AS organizerName 
                               FROM event
                               JOIN event_attendee ON event.eventID = event_attendee.eventID
                               JOIN organizer ON event.organizerID = organizer.organizerID
                               WHERE event_attendee.attendeeID = ?");
$events_stmt->bind_param("i", $attendeeID);
$events_stmt->execute();
$events_result = $events_stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Registered Events</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Attendee)</h2>
    <p>You have access to attendee features.</p>

    <h3>Your Registered Events</h3>
    <table border="1" cellpadding="5">
        <tr><th>Event Name</th><th>Date</th><th>Organizer</th><th>Description</th><th>Action</th></tr>

        <?php while ($event = $events_result->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($event['eventName']); ?></strong></td>
                <td><?php echo htmlspecialchars($event['date']); ?></td>
                <td><?php echo htmlspecialchars($event['organizerName']); ?></td>
                <td><?php echo htmlspecialchars($event['description']); ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="eventID" value="<?php echo $event['eventID']; ?>">
                        <input type="submit" value="Unregister">
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>

    </table>
    <br><a href="index.php">Register For an event!</a>
    <br><a href="logout.php">Logout</a>
</body>
</html>