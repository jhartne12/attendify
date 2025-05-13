<?php

//connection string
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendify";
$conn;


// Internal APIs 
function openDB() {
  global $servername, $username, $password, $dbname, $conn;

// Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  if ($conn->connect_error)
    return $conn->connect_error;
  else
    return "Connected";
}

function closeDB() {
  global $conn;
  $conn->close();
}

// API to modify DB
function modifyDB($sql) {
  global $conn;
  $message = openDB();
  if ($message == "Connected") {
    if ($conn->query($sql) === TRUE)
      $message = "Update Successful";
    else
      $message = $conn->error;
    closeDB();
  }
  return $message . "<br>";
}

// API to query DB
function queryDB($sql) { // returns an object or a string
  global $conn;
  $message = openDB();
  if ($message == "Connected") {
    $result = $conn->query($sql);
    if (gettype($result) == "object")
      $message = $result;
    else
      $message = $conn->error . "<br>Your SQL:" . $sql;
    closeDB();
  }
  return $message;
}

// API for login with prepared statement
function loginDB($sql, $user, $pwd) {
  global $conn;
  $message = openDB();
  if ($message == "Connected") {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (gettype($result) == "object") {
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashed_password = $row['password'];

                if (password_verify($pwd, $hashed_password)) {
                    $message = $result;
                }
            }
    } else {
          $message = $conn->error . "<br>Your SQL:" . $sql;
    }

    closeDB();
  }
  return $message;
}
?>
