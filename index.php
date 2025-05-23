<?php
session_start();
include('DBConnect.php');
global $conn;

// ensure the database connection is open
$connMessage = openDB();
if ($connMessage !== "Connected") {
    die("Database connection failed: " . htmlspecialchars($connMessage));
}

// handle filters
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : null;
$keywordFilter = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : null;
$organizerFilter = isset($_GET['organizer']) ? htmlspecialchars($_GET['organizer']) : null;

$sql = "SELECT event.eventID, event.Name AS eventName, event.date, event.address, event.description, event.categoryID, organizer.Name AS organizerName 
        FROM event 
        JOIN organizer ON event.organizerID = organizer.organizerID 
        WHERE 1=1";

if ($categoryFilter) {
    $sql .= " AND event.categoryID = $categoryFilter";
}
if ($keywordFilter) {
    $sql .= " AND (event.Name LIKE '%$keywordFilter%' OR event.description LIKE '%$keywordFilter%')";
}
if ($organizerFilter) {
    $sql .= " AND organizer.Name LIKE '%$organizerFilter%'";
}

$result = queryDB($sql);
if (gettype($result) !== "object") {
    die("Error fetching events: " . htmlspecialchars($result));
}
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
        <h3 style="font-size:50px" class="text-center">Welcome to Attendify</h3>
        <br>
        <button class="btn btn-info mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#filterForm" aria-expanded="false" aria-controls="filterForm">
            Show Filters
        </button>
        <div class="collapse w-75" id="filterForm">
            <h4 class="text-center">Filter Events</h4>
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">All</option>
                            <option value="1" <?= $categoryFilter == 1 ? 'selected' : '' ?>>Conference</option>
                            <option value="2" <?= $categoryFilter == 2 ? 'selected' : '' ?>>Workshop</option>
                            <option value="3" <?= $categoryFilter == 3 ? 'selected' : '' ?>>Seminar</option>
                            <option value="4" <?= $categoryFilter == 4 ? 'selected' : '' ?>>Meetup</option>
                            <option value="5" <?= $categoryFilter == 5 ? 'selected' : '' ?>>Webinar</option>
                            <option value="6" <?= $categoryFilter == 6 ? 'selected' : '' ?>>Social</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="keyword" class="form-label">Keyword</label>
                        <input type="text" id="keyword" name="keyword" class="form-control" value="<?= $keywordFilter ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="organizer" class="form-label">Organizer</label>
                        <input type="text" id="organizer" name="organizer" class="form-control" value="<?= $organizerFilter ?>">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col text-center">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="index.php" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>

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

                        $isAttendee = isset($_SESSION['role']) && $_SESSION['role'] == 'attendee';

                        echo "<tr>
                            <td><strong>{$row['eventName']}</strong><br><small class='text-muted'>by {$row['organizerName']}</small></td>
                            <td>{$row['date']}</td>
                            <td>{$row['address']}</td>
                            <td>{$row['description']}</td>
                            <td>{$category}</td>
                            <td>";

                        if ($isAttendee) {
                            echo "<form action='signup_event.php' method='POST'>
                                <input type='hidden' name='eventID' value='{$row['eventID']}'>
                                <button type='submit' class='btn btn-success'>Sign Up</button>
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
        
        fetch('fetch_notifications.php')
            .then(response => response.json())
            .then(data => {
                const notificationList = document.getElementById('notificationList');
                const notificationBadge = document.getElementById('notificationBadge');
                notificationList.innerHTML = '';

                if (data.length > 0) {
                    notificationBadge.textContent = data.length;
                    notificationBadge.style.display = 'inline-block';
                    data.forEach((notification, index) => {
                        const li = document.createElement('li');
                        li.innerHTML = `<a class="dropdown-item" href="#" onclick="markAsRead(${index})">${notification}</a>`;
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

        function markAsRead(notificationID) {
            fetch('update_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `notificationID=${notificationID}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Notification marked as read');
                } else {
                    console.error('Failed to mark notification as read:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

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

</body>
</html>
