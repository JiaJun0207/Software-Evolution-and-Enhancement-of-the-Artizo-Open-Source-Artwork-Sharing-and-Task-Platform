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
    if (isset($_SESSION["feedback"])) {
        echo $_SESSION["feedback"]; //to show the please fill in the form
    } ?>

    <form action="login_form.php" method="post">

        <label for="user_name">Username</label>
        <input type="text" name="user_name" id="user_name"> <!-- Added required for front end only -->
        <label for="password">Password</label>
        <input type="password" id="password" name="password"> <!-- Added required for front end only -->
        <button type="submit" class="btn btn-primary">Login</button>
        <p>Don't have an account? <a href="signup.php">Sign up</a></p>

    </form>


</body>

</html>