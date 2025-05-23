<?php
session_start();
include("DBConnect.php");
openDB();
global $conn;

// check if user is logged in and is an organizer
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'organizer') {
    header('Location: index.php');
    exit();
}

$email = $_SESSION['username'];

// fetch organizerId
$organizer_stmt = $conn->prepare("SELECT organizerID FROM organizer WHERE email = ?");
$organizer_stmt->bind_param("s", $email);
$organizer_stmt->execute();
$organizer_result = $organizer_stmt->get_result();

if ($organizer_result->num_rows == 0) {
    echo "<p style='color:red;'>Organizer not found.</p>";
    exit();
}

$organizer_row = $organizer_result->fetch_assoc();
$organizerID = $organizer_row['organizerID'];
$organizer_stmt->close();

// handle event deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eventID'])) {
    $event_id = intval($_POST['eventID']);

    // start transaction
    $conn->begin_transaction();

    try {
        // notify attendees about the deleted event
        $notify_stmt = $conn->prepare("
            INSERT INTO notifications (attendeeID, message)
            SELECT attendeeID, CONCAT('The event \"', (SELECT Name FROM event WHERE eventID = ?), '\" has been deleted.')
            FROM event_attendee
            WHERE eventID = ?
        ");
        $notify_stmt->bind_param("ii", $event_id, $event_id);
        $notify_stmt->execute();
        $notify_stmt->close();

        // notify the organizer about the deleted event
        $notify_organizer_stmt = $conn->prepare("
            INSERT INTO notifications (organizerID, message)
            VALUES (?, CONCAT('Your event \"', (SELECT Name FROM event WHERE eventID = ?), '\" has been deleted.'))
        ");
        $notify_organizer_stmt->bind_param("ii", $organizerID, $event_id);
        $notify_organizer_stmt->execute();
        $notify_organizer_stmt->close();

        // delete records from event_attendee table
        $delete_event_attendee_stmt = $conn->prepare("DELETE FROM event_attendee WHERE eventID = ?");
        $delete_event_attendee_stmt->bind_param("i", $event_id);
        $delete_event_attendee_stmt->execute();
        $delete_event_attendee_stmt->close();

        // delete the event
        $delete_event_stmt = $conn->prepare("DELETE FROM event WHERE eventID = ? AND organizerID = ?");
        $delete_event_stmt->bind_param("ii", $event_id, $organizerID);
        $delete_event_stmt->execute();
        $delete_event_stmt->close();

        // commit transaction
        $conn->commit();
        echo "<p style='color:green;'>Successfully deleted event #$event_id and its associated records. notifications sent to attendees.</p>";
    } catch (Exception $e) {
        // rollback transaction on error
        $conn->rollback();
        echo "<p style='color:red;'>Error deleting event: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// fetch events created by the organizer
$events_stmt = $conn->prepare("SELECT eventID, Name AS eventName, date, address, description, categoryID 
                               FROM event
                               WHERE organizerID = ?");
$events_stmt->bind_param("i", $organizerID);
$events_stmt->execute();
$events_result = $events_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Organizer Dashboard - Attendify</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" type="text/css" href="style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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
                        <a class="nav-link" href="event.php">Create Event</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profileinfo.php">Edit Profile</a>
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

    <div class="container mt-5">
        <h2 class="text-center">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Organizer)</h2>
        <p class="text-center">You have access to organizer features.</p>

        <h3 style="font-size:50px" class="text-center">Your Created Events</h3>
        <table class="table registration-table table-bordered">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Address</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($event = $events_result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($event['eventName']); ?></strong></td>
                        <td><?php echo htmlspecialchars($event['date']); ?></td>
                        <td><?php echo htmlspecialchars($event['address']); ?></td>
                        <td><?php echo htmlspecialchars($event['description']); ?></td>
                        <td>
                            <?php
                            switch ($event['categoryID']) {
                                case 1: echo "Conference"; break;
                                case 2: echo "Workshop"; break;
                                case 3: echo "Seminar"; break;
                                case 4: echo "Meetup"; break;
                                case 5: echo "Webinar"; break;
                                case 6: echo "Social"; break;
                                default: echo "Unknown";
                            }
                            ?>
                        </td>
                        <td>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                <input type="hidden" name="eventID" value="<?php echo $event['eventID']; ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>