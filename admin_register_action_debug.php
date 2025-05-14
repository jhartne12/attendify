<?php
session_start();
require "DBConnect.php";
?>
<head>
  <title>Admin Registration - Attendify</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <nav class="navbar navbar-expand-sm navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Attendify</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mynavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_register.php">Create User</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_events.php">Manage Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_profileinfo.php">Edit Profile</a>
                    </li>
                </ul>
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="welcome_<?php echo $_SESSION['role']; ?>.php" class="btn btn-primary">Welcome <?php echo htmlspecialchars($_SESSION['role']); ?>, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                    <a href="logout.php" class="btn btn-danger ms-2">Logout</a>
                <?php else: ?>
                    <a href="LogInPage.php" class="btn btn-primary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
<?php

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
    echo "Passwords do not match. Please go back and re-enter the user's passwords!";
    echo "<br><br>Redirecting back after 5 seconds.";
    echo "<script>
        setTimeout(function() {
        window.history.back();
        }, 5000);
        </script>";
    exit;
}

// checks if username is duplicate
$sql_check_uname = "SELECT * FROM attendee WHERE username = '$uname' UNION SELECT * FROM organizer WHERE username = '$uname' UNION SELECT * FROM admin WHERE username = '$uname'";
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
} elseif ($role == "Admin") {
    $sql = "INSERT INTO admin (name, username, email, password, securityQ, securityA) 
            VALUES ('$name', '$uname', '$email', '$hashed_password', '$security_question', '$security_answer')";
}else {
    echo "Invalid role selected, please go back and pick a role!";
    echo "<script>
        setTimeout(function() {
        window.history.back();
        }, 5000);
        </script>";
    exit;
}
echo modifyDB($sql) . "<br>User created! Redirecting you back to the Admin Dashboard.";
echo "<script>
    setTimeout(function() {
    window.location.href = 'welcome_admin.php';
    }, 5000);
    </script>";
echo "</div>";
?>
</body>
</html>
