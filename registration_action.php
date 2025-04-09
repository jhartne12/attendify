<?php

require "DBConnect.php";

// collect form data
$role = $_POST["role"];
$name = $_POST["name"];
$uname = $_POST["username"];
$email = $_POST["email"];
$passwd = $_POST["password"];
$passwd2 = $_POST["passwd2"];
$security_question = $_POST["security_question"];
$security_answer = $_POST["security_answer"];

// centers the errors and update requests
echo "<div style='text-align: center; font-size: 18px; margin-top: 20px;'>";

// checks if password is too short
if (strlen($passwd) < 8) {
    echo "Password must be at least 8 characters long!";
    echo "<br><br>Redirecting back after 5 seconds.";
    echo "<script>
        setTimeout(function() {
        window.history.back();
        }, 5000);
        </script>";
    exit;
}

// checks if password not typed in correctly
if ($passwd !== $passwd2) {
    echo "Passwords do not match. Please go back and re-enter your passwords!";
    echo "<br><br>Redirecting back after 5 seconds.";
    echo "<script>
        setTimeout(function() {
        window.history.back();
        }, 5000);
        </script>";
    exit;
}

// checks if username is duplicate
$sql_check_uname = "SELECT * FROM attendee WHERE username = '$uname' UNION SELECT * FROM organizer WHERE username = '$uname'";
$result_uname = queryDB($sql_check_uname);
if ($result_uname->num_rows > 0) {
    echo "Username already exists. Please choose a different username!";
    echo "<br><br>Redirecting back after 5 seconds.";
    echo "<script>
        setTimeout(function() {
        window.history.back();
        }, 5000);
        </script>";
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
    echo "<script>
        setTimeout(function() {
        window.history.back();
        }, 5000);
        </script>";
    exit;
}
echo modifyDB($sql) . "<br>Redirecting you to the Log In Page!";
echo "<script>
    setTimeout(function() {
    window.location.href = 'LogInPage.php';
    }, 5000);
    </script>";
echo "</div>";
?>