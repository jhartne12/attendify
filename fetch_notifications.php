<?php
session_start();
include("DBConnect.php");
openDB();
global $conn;

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo json_encode([]);
    exit();
}

$email = $_SESSION['username'];
$role = $_SESSION['role'];

$notifications = [];

if ($role === 'attendee') {
    $stmt = $conn->prepare("
        SELECT message 
        FROM notifications 
        WHERE attendeeID = (SELECT attendeeID FROM attendee WHERE email = ?) AND isRead = 0 
        ORDER BY created_at DESC
    ");
} elseif ($role === 'organizer') {
    $stmt = $conn->prepare("
        SELECT message 
        FROM notifications 
        WHERE organizerID = (SELECT organizerID FROM organizer WHERE email = ?) AND isRead = 0 
        ORDER BY created_at DESC
    "); 
} else {
    echo json_encode([]);
    exit();
}

if ($stmt) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row['message'];
    }
    $stmt->close();
}

echo json_encode($notifications);
closeDB();
?>
