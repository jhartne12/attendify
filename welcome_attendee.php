<?php
session_start();
include("DBConnect.php");
openDB();
global $conn;

// verify session
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

// fetch unread notifications for the attendee
$notifications = [];
$notification_stmt = $conn->prepare("
    SELECT message 
    FROM notifications 
    WHERE attendeeID = ? AND isRead = 0 
    ORDER BY created_at DESC
");
$notification_stmt->bind_param("i", $attendeeID);
$notification_stmt->execute();
$notification_result = $notification_stmt->get_result();
while ($row = $notification_result->fetch_assoc()) {
    $notifications[] = $row['message'];
}
$notification_stmt->close();

// mark notifications as read
$mark_read_stmt = $conn->prepare("UPDATE notifications SET isRead = 1 WHERE attendeeID = ?");
$mark_read_stmt->bind_param("i", $attendeeID);
$mark_read_stmt->execute();
$mark_read_stmt->close();

// handle event unregistration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eventID'])) {
    $event_id = intval($_POST['eventID']);

    // remove the attendee from the event using the attendeeID
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
<html lang="en">
<head>
  <title>Attendee Dashboard - Attendify</title>
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
                    <?php if (!empty($notifications)): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Notifications
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="notificationDropdown">
                                <?php foreach ($notifications as $notification): ?>
                                    <li><a class="dropdown-item" href="#"><?php echo htmlspecialchars($notification); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Event Register</a>
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
        <h2 class="text-center">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Attendee)</h2>
        <p class="text-center">You have access to attendee features.</p>

        <h3 style="font-size:50px" class="text-center">Your Registered Events</h3>
        <table class="table registration-table table-bordered">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Organizer</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($event = $events_result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($event['eventName']); ?></strong></td>
                        <td><?php echo htmlspecialchars($event['date']); ?></td>
                        <td><?php echo htmlspecialchars($event['organizerName']); ?></td>
                        <td><?php echo htmlspecialchars($event['description']); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="eventID" value="<?php echo $event['eventID']; ?>">
                                <button type="submit" class="btn btn-danger">Unregister</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>