<?php

// Connection parameters
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "attendify";

// Create a global variable for connection
$conn = null;

// Internal APIs 

// Open Database Connection
function openDB($servername, $username, $password, $dbname) {
  global $conn;
  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check for connection errors
  if ($conn->connect_error) {
    return "Connection failed: " . $conn->connect_error;
  }
  return "Connected";
}

// Close Database Connection
function closeDB() {
  global $conn;
  if ($conn) {
    $conn->close();
  }
}

// Modify Database (Insert, Update, Delete)
function modifyDB($sql, $servername, $username, $password, $dbname) {
  global $conn;
  $message = openDB($servername, $username, $password, $dbname);

  if ($message == "Connected") {
    if ($conn->query($sql) === TRUE) {
      $message = "Update Successful";
    } else {
      $message = "Error: " . $conn->error;
    }
    closeDB();
  }
  return $message . "<br>";
}

// Query Database (Select)
function queryDB($sql, $servername, $username, $password, $dbname) {
  global $conn;
  $message = openDB($servername, $username, $password, $dbname);

  if ($message == "Connected") {
    $result = $conn->query($sql);
    if ($result) {
      $message = $result;
    } else {
      $message = "Error: " . $conn->error . "<br>Your SQL: " . $sql;
    }
    closeDB();
  }
  return $message;
}

// Login using prepared statements (for secure login)
function loginDB($sql, $user, $pwd, $servername, $username, $password, $dbname) {
  global $conn;
  $message = openDB($servername, $username, $password, $dbname);

  if ($message == "Connected") {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
      return "Error preparing statement: " . $conn->error;
    }

    $stmt->bind_param("ss", $user, $pwd);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
      // Assuming you want to return the first row
      $message = $result->fetch_assoc();  // Fetch the first row
    } else {
      $message = "No matching records found.";
    }

    $stmt->close();
    closeDB();
  }
  return $message;
}

?>
