<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registration</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <link href="registration.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="javascript:void(0)">Attendify</a>
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
                    <form class="d-flex">
                        <input class="form-control me-2" type="text" placeholder="Search">
                        <button class="btn btn-primary" type="button">Search</button>
                    </form>
                </div>
            </div>
        </nav>

        <div class="registration-container">
            <form action="registration_action.php" method="POST">
                <table class="table registration-table table-bordered">
                    <thead>
                        <tr>
                            <th colspan="2">Registration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2">
                                <label class="form-label">Role</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="role" id="attendee" value="Attendee" required>
                                    <label class="form-check-label" for="attendee">Attendee</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="role" id="organizer" value="Organizer" required>
                                    <label class="form-check-label" for="organizer">Organizer</label>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td><label for="name" class="form-label">Full Name:</label></td>
                            <td><input type="text" class="form-control" id="name" placeholder="Enter your full name" name="name" required></td>
                        </tr>

                        <tr>
                            <td><label for="username" class="form-label">Username:</label></td>
                            <td><input type="text" class="form-control" id="username" placeholder="Choose a username" name="username" required></td>
                        </tr>

                        <tr>
                            <td><label for="email" class="form-label">Email:</label></td>
                            <td><input type="email" class="form-control" id="email" placeholder="Enter email" name="email" required></td>
                        </tr>

                        <tr>
                            <td><label for="password" class="form-label">Password:</label></td>
                            <td><input type="password" class="form-control" id="password" placeholder="Enter password" name="password" required></td>
                        </tr>

                        <tr>
                            <td><label for="security-question" class="form-label">Security Question:</label></td>
                            <td><input type="text" class="form-control" id="security-question" placeholder="Enter your security question" name="security_question" required></td>
                        </tr>

                        <tr>
                            <td><label for="security-answer" class="form-label">Security Answer:</label></td>
                            <td><input type="text" class="form-control" id="security-answer" placeholder="Answer to your security question" name="security_answer" required></td>
                        </tr>

                        <tr>
                            <td colspan="2" class="text-center">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
    </body>
</html>
