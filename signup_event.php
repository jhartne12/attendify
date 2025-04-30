<?php
session_start();
include 'DBConnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['username']) || !isset($_POST['eventID'])) {
        echo "<p style='text-align:center; color:red;'>Unauthorized or missing information.</p>";
        exit;
    }

    $email = $_SESSION['username'];
    $eventId = intval($_POST['eventID']);

    $connMessage = openDB();
    global $conn;

    if ($connMessage !== "Connected") {
        echo "<p style='text-align:center; color:red;'>Database connection failed: " . htmlspecialchars($connMessage) . "</p>";
        exit;
    }

    $stmt = $conn->prepare("SELECT attendeeID FROM attendee WHERE email = ?");
    if (!$stmt) {
        echo "<p style='text-align:center; color:red;'>Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
        closeDB();
        exit;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        echo "<p style='text-align:center; color:red;'>Attendee not found.</p>";
        $stmt->close();
        closeDB();
        exit;
    }

    $row = $result->fetch_assoc();
    $attendeeID = $row['attendeeID'];
    $stmt->close();

    $checkStmt = $conn->prepare("SELECT * FROM event_attendee WHERE attendeeID = ? AND eventID = ?");
    if (!$checkStmt) {
        echo "<p style='text-align:center; color:red;'>Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
        closeDB();
        exit;
    }
    $checkStmt->bind_param("ii", $attendeeID, $eventId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult && $checkResult->num_rows > 0) {
        echo "<p style='text-align:center; color:red;'>You have already signed up for this event!</p>";
        $checkStmt->close();
        closeDB();
        showLinks();
        exit;
    }
    $checkStmt->close();

    $insertSql = "INSERT INTO event_attendee (attendeeID, eventID) VALUES ($attendeeID, $eventId)";
    $message = modifyDB($insertSql);

    if (strpos($message, "Update Successful") !== false) {
        echo "<p style='text-align:center; color:green;'>Successfully signed up for the event!</p>";
    } else {
        echo "<p style='text-align:center; color:red;'>Error signing up: " . htmlspecialchars($message) . "</p>";
    }

    showLinks();
}

// helper function for navigation
function showLinks() {
    echo "<div style='text-align:center; margin-top:20px;'>
            <a href='index.php'>Back to Events</a> | 
            <a href='welcome_attendee.php'>Back to Dashboard</a>
          </div>";
}
?>
