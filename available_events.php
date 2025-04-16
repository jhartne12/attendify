<?php
session_start();
include("DBConnect.php");

if (!isset($_SESSION["email"]) || $_SESSION["role"] !== "attendee") {
    header("Location: LogInPage.php");
    exit();
}

$sql = "SELECT * FROM event ORDER BY date ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Available Events</title>
</head>
<body>
  <h2>Available Events</h2>
  <table border="1">
    <tr>
      <th>Name</th><th>Date</th><th>Address</th><th>Description</th><th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row["Name"]) ?></td>
      <td><?= htmlspecialchars($row["date"]) ?></td>
      <td><?= htmlspecialchars($row["address"]) ?></td>
      <td><?= htmlspecialchars($row["description"]) ?></td>
      <td>
        <form method="post" action="signup_event.php">
          <input type="hidden" name="event_id" value="<?= $row['id'] ?>">
          <button type="submit">Sign Up</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
