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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <style>
      .btn-outline-black {
        border-color: #000 !important;
        color: #000 !important;
      }
      .btn-outline-black:hover, .btn-outline-black.active {
        background-color: #000 !important;
        color: #fff !important;
        border-color: #000 !important;
      }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row vh-100">
            <div class="col-md-6 d-flex justify-content-center align-items-center bg-white">
                <div class="text-center" style="width: 100%; max-width: 400px;">

                    <img src="assets/logo/onboarding_logo.png" alt="Logo" class="img-fluid mb-4">

                    <div class="d-grid gap-2 d-md-block mb-4">
                        <a href="login.php" class="btn btn-outline-black<?php if(basename($_SERVER['PHP_SELF'])=='login.php'){echo ' active';} ?>">Login</a>
                        <a href="signup.php" class="btn btn-outline-black<?php if(basename($_SERVER['PHP_SELF'])=='signup.php'){echo ' active';} ?>">Sign Up</a>
                    </div>

                    <?php
                    if (isset($_SESSION["feedback"])) {
                        echo $_SESSION["feedback"];
                    } ?>


                    <form action="login_form.php" method="post">
                        <input type="text" name="user_name" id="user_name" class="form-control mb-3" placeholder="Username">
                        <input type="password" id="password" name="password" class="form-control mb-3" placeholder="Password">
                        <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                        <p>Don't have an account? <a href="signup.php">Sign up</a></p>
                    </form>

                </div>

            </div>

            <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center p-0"
                style="background: #f8f9fa;">
                <img src="assets/onboarding/image.jpg" alt="Image description"
                    style="width: 100%; height: 100vh; object-fit: cover;">
            </div>
        </div>
    </div>

</body>

</html>