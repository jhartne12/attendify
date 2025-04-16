<html lang="en">
<head>
  <title>Attendify</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body style="height:1500px">

<nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
  <a class="navbar-brand" href="index.php">Attendify</a>
  <ul class="navbar-nav">
    <?php if (isset($_SESSION["email"])): ?>
      <li class="nav-item">
        <a class="nav-link" href="#">Welcome, <?= htmlspecialchars($_SESSION["email"]) ?></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="Logout.php">Log Out</a>
      </li>
    <?php else: ?>
      <li class="nav-item">
        <a class="nav-link" href="registration.php">Register</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="LogInPage.php">Log In</a>
      </li>
    <?php endif; ?>
  </ul>
</nav>


<div class="container-fluid" style="margin-top:80px">
  <h3 style="font-size:50px">Welcome to Attendify</h3>
  <p></p>
  <h1></h1>
</div>
    <footer>
        <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li> 
    </footer>
</body>
</html>