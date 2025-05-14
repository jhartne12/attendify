<?php
session_start();
require "DBConnect.php";
?>
<head>
  <title>Attendify</title>
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
<?php
// only allow admins to delete events
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eventID'])) {
    $eventID = intval($_POST['eventID']);

    $connMessage = openDB();
    global $conn;

    if ($connMessage !== "Connected") {
        echo "<p style='text-align:center; color:red;'>database connection failed: " . htmlspecialchars($connMessage) . "</p>";
        exit;
    }

    // begin transaction
    $conn->begin_transaction();

    try {
        // notify attendees about the deleted event
        $notify_stmt = $conn->prepare("
            INSERT INTO notifications (attendeeID, message)
            SELECT attendeeID, CONCAT('The event \"', (SELECT Name FROM event WHERE eventID = ?), '\" has been deleted.')
            FROM event_attendee
            WHERE eventID = ?
        ");
        $notify_stmt->bind_param("ii", $eventID, $eventID);
        $notify_stmt->execute();
        $notify_stmt->close();

        // notify the organizer about the deleted event
        $notify_organizer_stmt = $conn->prepare("
            INSERT INTO notifications (organizerID, message)
            SELECT organizerID, CONCAT('Your event \"', (SELECT Name FROM event WHERE eventID = ?), '\" has been deleted.')
            FROM event
            WHERE eventID = ?
        ");
        $notify_organizer_stmt->bind_param("ii", $eventID, $eventID);
        $notify_organizer_stmt->execute();
        $notify_organizer_stmt->close();

        // delete related signups
        $stmt1 = $conn->prepare("DELETE FROM event_attendee WHERE eventID = ?");
        $stmt1->bind_param("i", $eventID);
        $stmt1->execute();
        $stmt1->close();

        // delete the event itself
        $stmt2 = $conn->prepare("DELETE FROM event WHERE eventID = ?");
        $stmt2->bind_param("i", $eventID);
        $stmt2->execute();
        $stmt2->close();

        // commit transaction
        $conn->commit();
        echo "<p style='text-align:center; color:green;'>event and all related signups have been successfully deleted. notifications sent to attendees.</p>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<p style='text-align:center; color:red;'>failed to delete event: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    closeDB();

    echo "<div style='text-align:center; margin-top:20px;'>
            <a href='admin_events.php'>back to event list</a>
          </div>";
} else {
    echo "<p style='text-align:center; color:red;'>invalid request.</p>";
}
?>
</body>
</html>
