<?php
include("config.php");// Include the database connection file

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

include("navbar.php"); // Include the navigation bar
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid" style="padding-left: 60px; padding-right: 60px; margin-top:60px;">
        <h1 class="inter-bold-44 mb-4" style="margin-top:60px;">Support</h1>
        <div class="row">
            <div class="col">
                <div class="mb-3">
                    <form action="support_form.php" method="POST" enctype="multipart/form-data">
                        <div class=" align-items-center gap-3 mb-3" style="padding-left:40px; padding-right:40px;">
                            <div class="mb-4">
                                <label for="support_email" class="inter-bold-32 mb-3">Email</label>
                                <input type="email" class="form-control inter-medium-25 left-placeholder border_black"
                                    id="support_email" name="support_email" placeholder="Add a email" required>
                            </div>
                            <div class="mb-4">
                                <label for="support_phone" class="inter-bold-32 mb-3">Phone Number</label>
                                <input type="tel" class="form-control inter-medium-25 left-placeholder border_black"
                                    id="support_phone" name="support_phone" placeholder="Add a phone number" required>
                            </div>
                            <div class="mb-4">
                                <label for="support_description" class="inter-bold-32 mb-3">Message</label>
                                <textarea class="form-control inter-medium-25 left-placeholder border_black"
                                    id="support_description" name="support_description" rows="3"
                                    placeholder="Add a message" required></textarea>
                            </div>
                            <div style="padding-bottom: 60px;">
                                <button type="submit" class="btn btn-outline-black inter-medium-24 active">
                                    <img src="assets/icons/post.png" alt="post Icon"
                                        style="width:20px; height:20px; margin-right:8px; vertical-align:middle;">
                                    Post
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col">
                <div class="mb-3">
                    <div class=" align-items-center gap-3 mb-3" style="padding-left:40px; padding-right:40px;">
                        <div class="mb-4">
                            <label for="support_email" class="inter-bold-32 mb-3">Contact</label>
                            <p class="inter-extralight-24">Contact us questions, technical assistance, or collaboration
                                opportunities via the contact information provided.</p>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex">
                                <a type="hidden" href="mailto:cs-artizo@gmail.com">
                                    <img src="assets/icons/email.png" alt="Support Icon"
                                    style="width:30px; height:30px; display:block; margin-right: 10px;"></a>
                                    <p class="inter-extralight-24">cs-artizo@gmail.com</p>
                                </div>
                                <div class="d-flex">
                                    <a type="hidden" href="tel:+60169125204">
                                        <img src="assets/icons/phone.png" alt="Support Icon"
                                            style="width:30px; height:30px; display:block; margin-right: 10px;"></a>
                                        <p class="inter-extralight-24">+60 16-9125204</p>
                                </div>
                                <div class="d-flex">
                                    <a type="hidden" href="http://wa.link/y4xnnz">
                                        <img src="assets/icons/whatsapp.png" alt="Support Icon"
                                            style="width:30px; height:30px; display:block; margin-right: 10px;"></a>
                                        <p class="inter-extralight-24">http://wa.link/y4xnnz</p>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

</body>
<footer>
    <?php include("footer.php"); // Include the footer ?>
</footer>

</html>