<?php
session_start();
require "DBConnect.php";

// Only allow admins to delete events
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eventID'])) {
    $eventID = intval($_POST['eventID']);

    $connMessage = openDB();
    global $conn;

    if ($connMessage !== "Connected") {
        echo "<p style='text-align:center; color:red;'>Database connection failed: " . htmlspecialchars($connMessage) . "</p>";
        exit;
    }

    // Delete related signups
    $stmt1 = $conn->prepare("DELETE FROM event_attendee WHERE eventID = ?");
    if (!$stmt1) {
        echo "<p style='text-align:center; color:red;'>Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
        closeDB();
        exit;
    }
    $stmt1->bind_param("i", $eventID);
    $stmt1->execute();
    $stmt1->close();

    // Delete the event itself
    $stmt2 = $conn->prepare("DELETE FROM event WHERE eventID = ?");
    if (!$stmt2) {
        echo "<p style='text-align:center; color:red;'>Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
        closeDB();
        exit;
    }
    $stmt2->bind_param("i", $eventID);
    if ($stmt2->execute()) {
        echo "<p style='text-align:center; color:green;'>Event and all related signups have been successfully deleted.</p>";
    } else {
        echo "<p style='text-align:center; color:red;'>Failed to delete event: " . htmlspecialchars($stmt2->error) . "</p>";
    }
    $stmt2->close();

    closeDB();

    echo "<div style='text-align:center; margin-top:20px;'>
            <a href='index.php'>Back to Event List</a>
          </div>";
} else {
    echo "<p style='text-align:center; color:red;'>Invalid request.</p>";
}
?>
