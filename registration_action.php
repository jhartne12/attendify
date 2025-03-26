<?php

require "DBConnect.php";

// collect form data
$role = $_POST["role"];
$name = $_POST["name"];
$uname = $_POST["username"];
$email = $_POST["email"];
$passwd = $_POST["password"];
$security_question = $_POST["security_question"];
$security_answer = $_POST["security_answer"];

// checks if password is too short
if (strlen($passwd) < 8) {
    echo "Password must be at least 8 characters long!";
    exit;
}

// checks if username is duplicate
$sql_check_uname = "SELECT * FROM attendee WHERE username = '$uname' UNION SELECT * FROM organizer WHERE username = '$uname'";
$result_uname = queryDB($sql_check_uname);
if ($result_uname->num_rows > 0) {
    echo "Username already exists. Please choose a different username!";
    exit;
}


// hashes password
$hashed_password = password_hash($passwd, PASSWORD_DEFAULT);

if ($role == "Attendee") {
    $sql = "INSERT INTO attendee (name, username, email, password, securityQ, securityA) 
            VALUES ('$name', '$uname', '$email', '$hashed_password', '$security_question', '$security_answer')";
} elseif ($role == "Organizer") {
    $sql = "INSERT INTO organizer (name, username, email, password, securityQ, securityA) 
            VALUES ('$name', '$uname', '$email', '$hashed_password', '$security_question', '$security_answer')";
} else {
    echo "Invalid role selected, please go back and pick a role!";
    exit;
}
echo modifyDB($sql) . "<br>Use back button to return";
?>