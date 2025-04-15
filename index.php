<?php
session_start();
include('DBConnect.php');

// Query to fetch the events
$sql = "SELECT eventID, Name, date, address, description, categoryID FROM event";
$result = queryDB($sql);
?>

<html lang="en">
<head>
  <title>Attendify</title>
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
                        <a class="nav-link" href="javascript:void(0)">Link</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0)">Link</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0)">Link</a>
                    </li>
                </ul>
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="welcome_<?php echo $_SESSION['role']; ?>.php" class="btn btn-primary">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                    <a href="logout.php" class="btn btn-danger ms-2">Logout</a>
                <?php else: ?>
                    <a href="LogInPage.php" class="btn btn-primary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="margin-top:10px">
        <h3 style="font-size:50px">Welcome to Attendify</h3>
        <br>
        <h4 class="text-center">Available Events</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Address</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $category = '';
                        switch ($row['categoryID']) {
                            case 1: $category = 'Conference'; break;
                            case 2: $category = 'Workshop'; break;
                            case 3: $category = 'Seminar'; break;
                            case 4: $category = 'Meetup'; break;
                            case 5: $category = 'Webinar'; break;
                            case 6: $category = 'Social'; break;
                            default: $category = 'Unknown'; break;
                        }
                        
                        $isAttendee = isset($_SESSION['role']) && $_SESSION['role'] == 'Attendee';

                        echo "<tr>
                                <td>{$row['Name']}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['address']}</td>
                                <td>{$row['description']}</td>
                                <td>{$category}</td>
                                <td>";

                        if ($isAttendee) {
                            echo "<form action='signup_event.php' method='POST'>
                                    <input type='hidden' name='eventID' value='{$row['eventID']}'>
                                    <button type='submit' class='btn btn-success'>Sign Up</button>
                                  </form>";
                        } else {
                            echo "<button class='btn btn-secondary' disabled data-bs-toggle='tooltip' data-bs-placement='top' title='Register to sign up for events'>Sign Up</button>";    
                        }
                        
                        echo "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No events available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <footer>
        <li class="nav-item">
            <a class="nav-link" href="logout.php">Logout</a>
        </li> 
    </footer>

    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>

</body>
</html>
