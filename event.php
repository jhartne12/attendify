<?php
session_start();
include('DBConnect.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'organizer') {
    header('Location: index.php');
    exit();
}

// Fetch unread notifications for the logged-in user
$notifications = [];
if (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    $email = $_SESSION['username'];
    $role = $_SESSION['role'];

    if ($role === 'attendee') {
        $stmt = $conn->prepare("
            SELECT message 
            FROM notifications 
            WHERE attendeeID = (SELECT attendeeID FROM attendee WHERE email = ?) AND isRead = 0 
            ORDER BY created_at DESC
        ");
    } elseif ($role === 'organizer') {
        $stmt = $conn->prepare("
            SELECT message 
            FROM notifications 
            WHERE organizerID = (SELECT organizerID FROM organizer WHERE email = ?) AND isRead = 0 
            ORDER BY created_at DESC
        ");
    }

    if (isset($stmt)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row['message'];
        }
        $stmt->close();

        // Mark notifications as read
        if ($role === 'attendee') {
            $mark_read_stmt = $conn->prepare("
                UPDATE notifications 
                SET isRead = 1 
                WHERE attendeeID = (SELECT attendeeID FROM attendee WHERE email = ?)
            ");
        } elseif ($role === 'organizer') {
            $mark_read_stmt = $conn->prepare("
                UPDATE notifications 
                SET isRead = 1 
                WHERE organizerID = (SELECT organizerID FROM organizer WHERE email = ?)
            ");
        }

        if (isset($mark_read_stmt)) {
            $mark_read_stmt->bind_param("s", $email);
            $mark_read_stmt->execute();
            $mark_read_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registration</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <link href="style.css" rel="stylesheet" type="text/css">
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

        <div class="registration-container">
            <form action="event_action.php" method="POST">
                <table class="table registration-table table-bordered">
                    <thead>
                        <tr>
                            <th colspan="2">Create New Event</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><label for="event_name" class="form-label">Event Name:</label></td>
                            <td><input type="text" class="form-control" id="event_name" name="event_name" placeholder="Enter event name" required></td>
                        </tr>

                        <tr>
                            <td><label for="event_date" class="form-label">Date:</label></td>
                            <td><input type="date" class="form-control" id="event_date" name="event_date" required></td>
                        </tr>
                        
                        <tr>
                            <td><label for="event_time" class="form-label">Time:</label></td>
                            <td><input type="time" class="form-control" id="event_time" name="event_time" required></td>
                        </tr>

                        <tr>
                            <td><label for="event_address" class="form-label">Address:</label></td>
                            <td><input type="text" class="form-control" id="event_address" name="event_address" placeholder="Enter event address" required></td>
                        </tr>

                        <tr>
                            <td><label for="event_description" class="form-label">Description:</label></td>
                            <td><textarea class="form-control" id="event_description" name="event_description" rows="3" placeholder="Enter event description" required></textarea></td>
                        </tr>
                        
                        <tr>
                            <td><label for="event_category" class="form-label">Category:</label></td>
                            <td>
                                <select class="form-select" name="event_category" id="event_category" required>
                                    <option value="" disabled selected>Select a category</option>
                                    <option value="1">Conference</option>
                                    <option value="2">Workshop</option>
                                    <option value="3">Seminar</option>
                                    <option value="4">Meetup</option>
                                    <option value="5">Webinar</option>
                                    <option value="6">Social</option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <td colspan="2" class="text-center">
                                <button type="submit" class="btn btn-success">Create Event</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        <script>
    document.addEventListener("DOMContentLoaded", function() {
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = today.getMonth() + 1;
        var dd = today.getDate();
        if (mm < 10) { mm = '0' + mm; }
        if (dd < 10) { dd = '0' + dd; }
        var todayDate = yyyy + '-' + mm + '-' + dd;

        document.getElementById("event_date").setAttribute("min", todayDate);
    });
        </script>
    </body>
</html>
