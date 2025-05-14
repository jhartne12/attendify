<?php
session_start();
include 'DBConnect.php';

// Fetch unread notifications for the attendee
$notifications = [];
if (isset($_SESSION['username']) && $_SESSION['role'] === 'attendee') {
    $connMessage = openDB();
    global $conn;

    if ($connMessage === "Connected") {
        $stmt = $conn->prepare("SELECT attendeeID FROM attendee WHERE email = ?");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        $attendeeID = $result->fetch_assoc()['attendeeID'];
        $stmt->close();

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

        // Mark notifications as read
        $mark_read_stmt = $conn->prepare("UPDATE notifications SET isRead = 1 WHERE attendeeID = ?");
        $mark_read_stmt->bind_param("i", $attendeeID);
        $mark_read_stmt->execute();
        $mark_read_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
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

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['username']) || !isset($_POST['eventID'])) {
        echo "<p style='text-align:center; color:red;'>Unauthorized or missing information.</p>";
        exit;
    }

    $email = $_SESSION['username'];
    $eventId = intval($_POST['eventID']);

    $connMessage = openDB();
    global $conn;

    if ($connMessage !== "Connected") {
        echo "<p style='text-align:center; color:red;'>Database connection failed: " . htmlspecialchars($connMessage) . "</p>";
        exit;
    }

    $stmt = $conn->prepare("SELECT attendeeID FROM attendee WHERE email = ?");
    if (!$stmt) {
        echo "<p style='text-align:center; color:red;'>Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
        closeDB();
        exit;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        echo "<p style='text-align:center; color:red;'>Attendee not found.</p>";
        $stmt->close();
        closeDB();
        exit;
    }

    $row = $result->fetch_assoc();
    $attendeeID = $row['attendeeID'];
    $stmt->close();

    $checkStmt = $conn->prepare("SELECT * FROM event_attendee WHERE attendeeID = ? AND eventID = ?");
    if (!$checkStmt) {
        echo "<p style='text-align:center; color:red;'>Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
        closeDB();
        exit;
    }
    $checkStmt->bind_param("ii", $attendeeID, $eventId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult && $checkResult->num_rows > 0) {
        echo "<p style='text-align:center; color:red;'>You have already signed up for this event!</p>";
        $checkStmt->close();
        closeDB();
        showLinks();
        exit;
    }
    $checkStmt->close();

    $insertSql = "INSERT INTO event_attendee (attendeeID, eventID) VALUES ($attendeeID, $eventId)";
    $message = modifyDB($insertSql);

    if (strpos($message, "Update Successful") !== false) {
        echo "<p style='text-align:center; color:green;'>Successfully signed up for the event!</p>";
    } else {
        echo "<p style='text-align:center; color:red;'>Error signing up: " . htmlspecialchars($message) . "</p>";
    }

    showLinks();
}

// helper function for navigation
function showLinks() {
    echo "<div style='text-align:center; margin-top:20px;'>
            <a href='index.php'>Back to Events</a> | 
            <a href='welcome_attendee.php'>Back to Dashboard</a>
          </div>";
}
?>
</body>
</html>