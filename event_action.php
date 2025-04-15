<?php

require "DBConnect.php";

// collect form data
$event_name = $_POST["event_name"];
$event_date = $_POST["event_date"];
$event_address = $_POST["event_address"];
$event_description = $_POST["event_description"];
$event_category = $_POST["event_category"];
$organizer_id = $_SESSION['organizerID'];

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
$sql = "INSERT INTO event (organizerID, Name, date, address, description, categoryID) 
        VALUES ('$organizer_id', '$event_name', '$event_date', '$event_address', '$event_description', '$event_category')";

// Execute query and check if it was successful
if (modifyDB($sql)) {
    echo "Event created successfully!<br>Redirecting you to the event list page.";
    echo "<script>
        setTimeout(function() {
        window.location.href = 'event_list.php';
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
