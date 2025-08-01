<?php
session_start(); // to start a session
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>

<body>

    <?php
    include "navbar.php";
    if (isset($_SESSION["feedback"])) {
        echo $_SESSION["feedback"]; //to show the please fill i the form
    } ?>

    <form action="signup_form.php" method="post">

        <label for="username">Username</label>
        <input type="text" name="username" id="username"> <!-- Added required for front end only -->
        <label for="email">Email address</label>
        <input type="email" id="email" name="email"> <!-- Added required for front end only -->
        <label for="password">Password</label>
        <input type="password" id="password" name="password"> <!-- Added required for front end only -->
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password"> <!-- Added required for front end only -->
        <button type="submit" class="btn btn-primary">Sign Up</button>
        <p>Already have an account? <a href="login.php">Login</a></p>
        <p>By signing up, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</p>

    </form>


</body>

</html>