<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Your DB password if set
$db = 'attendify';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>