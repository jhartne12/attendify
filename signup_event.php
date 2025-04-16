<?php
session_start();
include("DBConnect.php");

if (!isset($_SESSION["email"]) || $_SESSION["role"] !== "attendee") {
    header("Location: LogInPage.php");
    exit();
}

$event_id = $_POST["event_id"];
$email = $_SESSION["email"];

// Prevent duplicate sign-ups
$check = $conn->prepare("SELECT * FROM event_attendees WHERE event_id = ? AND attendee_email = ?");
$check->bind_param("is", $event_id, $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "You already signed up for this event!";
} else {
    $stmt = $conn->prepare("INSERT INTO event_attendees (event_id, attendee_email) VALUES (?, ?)");
    $stmt->bind_param("is", $event_id, $email);
    if ($stmt->execute()) {
        echo "Successfully signed up!";
    } else {
        echo "Error signing up.";
    }
}
?>
