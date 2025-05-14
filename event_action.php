<?php
session_start();
require "DBConnect.php";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Event Creation - Attendify</title>
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

        <div class="text-center mt-5">
<?php
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'organizer') {
    header('Location: index.php');
    exit();
}

// collect form data
$event_name = $_POST["event_name"];
$event_date = $_POST["event_date"];
$event_time = $_POST["event_time"];
$event_datetime = $event_date . ' ' . $event_time;
$event_address = $_POST["event_address"];
$event_description = $_POST["event_description"];
$event_category = $_POST["event_category"];
$sql = "SELECT organizerID FROM organizer WHERE email = '" . $_SESSION['username'] . "'";
$result = queryDB($sql);
if (gettype($result) === "object" && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $organizer_id = $row['organizerID'];
} else {
    echo "Organizer not found.";
    exit;
}

// check if any required fields are empty
if (empty($event_name) || empty($event_date) || empty($event_address) || empty($event_description) || empty($event_category)) {
    echo "All fields are required!";
    echo "<br><br>Redirecting back after 5 seconds.";
    echo "<script>
        setTimeout(function() {
        window.history.back();
        }, 5000);
        </script>";
    exit;
}

// insert the event data into the database
$sql2 = "INSERT INTO event (organizerID, Name, date, address, description, categoryID) 
        VALUES ('$organizer_id', '$event_name', '$event_datetime', '$event_address', '$event_description', '$event_category')";

// execute query and check if it was successful
if (modifyDB($sql2)) {
    echo "Event created successfully!<br>Redirecting you to the front page.";
    echo "<script>
        setTimeout(function() {
        window.location.href = 'index.php';
        }, 5000);
        </script>";
} else {
    echo "There was an error creating the event! Please try again later.";
    echo "<br><br>Redirecting back after 5 seconds.";
    echo "<script>
        setTimeout(function() {
        window.history.back();
        }, 5000);
        </script>";
}
?>
</div>
</body>
</html>
