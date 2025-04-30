<?php

function openDB()
{
    $servername = "localhost";   // XAMPP default
    $username = "root";           // XAMPP default
    $password = "";               // No password in default XAMPP
    $dbname = "attendify";         // Your database name

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function modifyDB($query)
{
    $conn = openDB();
    if (!$conn) {
        die("Database connection failed.");
    }

    if ($conn->query($query) === TRUE) {
        return true;
    } else {
        // Log the error or display a safe message
        error_log("Database modification error: " . $conn->error);
        return false;
    }
}

function queryDB($query)
{
    $conn = openDB();
    if (!$conn) {
        die("Database connection failed.");
    }

    $result = $conn->query($query);
    if (!$result) {
        // Log the error or display a safe message
        error_log("Database query error: " . $conn->error);
        return false;
    }

    return $result;
}
?>
