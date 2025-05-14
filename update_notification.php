<?php
session_start();
include("DBConnect.php");
openDB();
global $conn;

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$email = $_SESSION['username'];
$role = $_SESSION['role'];

if (isset($_POST['markAll']) && $_POST['markAll'] === 'true') {
    if ($role === 'attendee') {
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET isRead = 1 
            WHERE attendeeID = (SELECT attendeeID FROM attendee WHERE email = ?)
        ");
    } elseif ($role === 'organizer') {
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET isRead = 1 
            WHERE organizerID = (SELECT organizerID FROM organizer WHERE email = ?)
        ");
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid role']);
        exit();
    }

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $success = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

closeDB();
?>
