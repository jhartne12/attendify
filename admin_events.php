<?php
session_start();
include('DBConnect.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Query to fetch the events
$sql = "SELECT event.eventID, event.Name AS eventName, event.date, event.address, event.description, event.categoryID, organizer.Name AS organizerName 
        FROM event 
        JOIN organizer ON event.organizerID = organizer.organizerID";

$result = queryDB($sql);
?>

<html lang="en">
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

    <div class="container-fluid d-flex flex-column align-items-center" style="margin-top:10px">
        <h3 style="font-size:50px" class="text-center">Admin Event List</h3>
        <br>
        <h4 class="text-center">Available Events</h4>
        <table class="table registration-table table-bordered w-75">
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
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $category = '';
                        switch ($row['categoryID']) {
                            case 1: $category = 'Conference';
                                break;
                            case 2: $category = 'Workshop';
                                break;
                            case 3: $category = 'Seminar';
                                break;
                            case 4: $category = 'Meetup';
                                break;
                            case 5: $category = 'Webinar';
                                break;
                            case 6: $category = 'Social';
                                break;
                            default: $category = 'Unknown';
                                break;
                        }

                        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';

                        echo "<tr>
                            <td><strong>{$row['eventName']}</strong><br><small class='text-muted'>by {$row['organizerName']}</small></td>
                            <td>{$row['date']}</td>
                            <td>{$row['address']}</td>
                            <td>{$row['description']}</td>
                            <td>{$category}</td>
                            <td>";

                        if ($isAdmin) {
                            echo "<form action='admin_events_action.php' method='POST'>"
                            . "<input type='hidden' name='eventID' value='" . htmlspecialchars($row['eventID']) . "'>
                                <button type='submit' class='btn btn-danger' onclick=\"return confirm('Are you sure you want to delete this event?');\">Remove</button>
                                </form>";
        } else {
                            echo "<button class='btn btn-secondary' disabled data-bs-toggle='tooltip' data-bs-placement='top'>Register to sign up!</button>";
                        }

                        echo "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No events available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>

</body>
</html>
