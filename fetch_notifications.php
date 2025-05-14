<?php
session_start();
include("DBConnect.php");

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo json_encode(['notifications' => []]);
    exit();
}

$email = $_SESSION['username'];
$role = $_SESSION['role'];

$connMessage = openDB();
global $conn;

if ($connMessage !== "Connected") {
    echo json_encode(['notifications' => []]);
    exit();
}

// fetch user ID based on role
$userID = null;
$idColumn = null;

if ($role === 'attendee') {
    $idColumn = 'attendeeID';
    $stmt = $conn->prepare("SELECT attendeeID AS userID FROM attendee WHERE email = ?");
} elseif ($role === 'organizer') {
    $idColumn = 'organizerID';
    $stmt = $conn->prepare("SELECT organizerID AS userID FROM organizer WHERE email = ?");
} else {
    echo json_encode(['notifications' => []]);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $userID = $row['userID'];
}
$stmt->close();

// fetch unread notifications
$notifications = [];
if ($userID !== null) {
    $stmt = $conn->prepare("SELECT message FROM notifications WHERE $idColumn = ? AND isRead = 0 ORDER BY created_at DESC");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();

    // mark notifications as read
    $stmt = $conn->prepare("UPDATE notifications SET isRead = 1 WHERE $idColumn = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->close();
}

closeDB();
echo json_encode(['notifications' => $notifications]);
?>
