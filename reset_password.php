<?php include("config.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container-fluid">
        <div class="row vh-100">
            <div class="col-md-6 d-flex justify-content-center align-items-center bg-white">
                <div class="text-center" style="width: 100%; max-width: 400px;">

                    <img src="assets/logo/onboarding_logo.png" alt="Logo" class="img-fluid mb-4">



                    <form action="reset_password_form.php" method="post">
                        <h2 class="inter-bold-32 mb-3">Reset Password</h2>
                        <p class="inter-extralight-15 mb-3">Enter a new password for your account.
                        </p>
                        <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
                        <input type="password" id="new_password" name="new_password" class="form-control mb-3 inter-medium-25"
                            placeholder="New Password">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control mb-3 inter-medium-25"
                            placeholder="Confirm Password">
                        <button type="submit" class="btn btn-outline-black w-100 mb-3 inter-medium-25">Send</button>
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