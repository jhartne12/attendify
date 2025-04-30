<?php
session_start();
require_once 'DBConnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['email']) && isset($_POST['event_id'])) {
        $attendeeEmail = $_SESSION['email'];
        $eventId = intval($_POST['event_id']);

        $conn = openDB();

        // Check if already signed up in event_registration table
        $check = $conn->prepare("SELECT * FROM event_registration WHERE attendee_email = ? AND event_id = ?");
        $check->bind_param("si", $attendeeEmail, $eventId);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            // Proceed with signup
            $stmt = $conn->prepare("INSERT INTO event_registration (attendee_email, event_id) VALUES (?, ?)");
            $stmt->bind_param("si", $attendeeEmail, $eventId);

            if ($stmt->execute()) {
                echo "<p style='text-align:center; color:green;'>Successfully signed up for the event!</p>";
            } else {
                echo "<p style='text-align:center; color:red;'>Error signing up: " . htmlspecialchars($stmt->error) . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p style='text-align:center; color:red;'>You have already signed up for this event!</p>";
        }

        $check->close();
        $conn->close();

        echo "<div style='text-align:center; margin-top:20px;'>
                <a href='event.php'>Back to Events</a> | <a href='welcome_attendee.php'>Back to Dashboard</a>
              </div>";
    } else {
        echo "<p style='text-align:center; color:red;'>Unauthorized or missing information.</p>";
    }
}
?>
