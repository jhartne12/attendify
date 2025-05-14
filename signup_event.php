<?php
session_start();
include 'DBConnect.php';
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
            <div class="collapse navbar-collapse" id="mynavbar">
                <ul class="navbar-nav me-auto">
                    <?php if (isset($_SESSION['username']) && ($_SESSION['role'] === 'attendee' || $_SESSION['role'] === 'organizer')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" onclick="markAllAsRead()">
                                Notifications
                                <span id="notificationBadge" class="badge bg-danger" style="display: none;"></span>
                            </a>
                            <ul class="dropdown-menu" id="notificationList" aria-labelledby="notificationDropdown">
                                <li><a class="dropdown-item text-muted" href="#">Loading...</a></li>
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
    <script>
        fetch('fetch_notifications.php')
            .then(response => response.json())
            .then(data => {
                const notificationList = document.getElementById('notificationList');
                const notificationBadge = document.getElementById('notificationBadge');
                notificationList.innerHTML = '';

                if (data.length > 0) {
                    notificationBadge.textContent = data.length;
                    notificationBadge.style.display = 'inline-block';
                    data.forEach(notification => {
                        const li = document.createElement('li');
                        li.innerHTML = `<a class="dropdown-item" href="#">${notification}</a>`;
                        notificationList.appendChild(li);
                    });
                } else {
                    notificationBadge.style.display = 'none';
                    const li = document.createElement('li');
                    li.innerHTML = '<a class="dropdown-item text-muted" href="#">No new notifications</a>';
                    notificationList.appendChild(li);
                }
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
            });

        function markAllAsRead() {
            fetch('update_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'markAll=true'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('All notifications marked as read');
                    document.getElementById('notificationBadge').style.display = 'none';
                } else {
                    console.error('Failed to mark notifications as read:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>

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