<?php
session_start();
require "DBConnect.php";

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

// centers the errors and update requests
echo "<div style='text-align: center; font-size: 18px; margin-top: 20px;'>";

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

// Execute query and check if it was successful
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

echo "</div>";
?>
