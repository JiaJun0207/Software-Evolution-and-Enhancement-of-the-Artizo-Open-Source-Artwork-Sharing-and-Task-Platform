<?php

include("config.php"); // Include the database connection file

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
// Fetch user data
$uid = $_SESSION['UID'];
$query = "SELECT `profile_image`, `user_name`, `user_description` FROM `user` WHERE `user_id` = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Determine profile image path
$profileImg = "assets/profile/user_profile.png";
if (!empty($user['profile_image'])) {
    $profileImg = "assets/profile/" . $user['profile_image'];
}

include("navbar.php"); // Include the navigation bar
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<Body>
    <div class="container-fluid px-3 px-md-5" style="margin-top:60px;">
        <div class="row flex-column flex-lg-row">
            <div class="col-12 col-lg-9 mb-3 mb-lg-0">
                <div class="card card_border mb-3">
                    <div class="card-body d-flex flex-row align-items-center px-3 px-md-5">
                        <h1 class="inter-bold-24 mt-2">Edit Profile</h1>
                    </div>
                    <div class="card-body d-flex flex-column flex-md-row align-items-center gap-3 mb-3 px-3 px-md-5">
                        <div class="d-inline-block mb-3 mb-md-0">
                            <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image"
                                class="rounded-circle" style="width:150px; height:150px; object-fit:cover;">
                        </div>
                        <div class="d-flex flex-row ms-md-auto align-items-end gap-3 w-100 w-md-auto justify-content-center justify-content-md-end">
                            <form action="edit_profile_form.php" method="POST" enctype="multipart/form-data" id="profileImgForm">
                                <input type="file" name="profile_image" accept="image/*" id="profileImgInput" style="display:none;" required>
                                <button type="button"
                                    class="btn btn-outline-black inter-medium-25 border_black"
                                    style="width:266px; height:53px;"
                                    onclick="document.getElementById('profileImgInput').click();">
                                    Upload Photo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="card card_border mb-2">
                    <div class="card-body d-flex flex-row justify-content-center align-items-center px-3 px-md-5">
                        <h1 class="inter-bold-24 mt-2 text-center w-100">Support & Security</h1>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center px-3 px-md-5">
                        <div class="mb-3 w-100 d-flex justify-content-center">
                            <a href="update_password.php" class="btn btn-outline-black inter-medium-25 border_black"
                                style="width:266px; height:53px;">Reset Password</a>
                        </div>
                        <div class="w-100 d-flex justify-content-center" style="margin-bottom: 44px;">
                            <a href="support.php" class="btn btn-outline-black inter-medium-25 border_black"
                                style="width:266px; height:53px;">Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card_border mb-5">
            <div class="card-body align-items-center gap-3 px-3 px-md-5">
                <h1 class="inter-bold-24 mt-2 mb-3">Description</h1>
                <form action="edit_profile_form.php" method="POST">
                    <div class="mb-4">
                        <textarea class="form-control inter-extralight-15 left-placeholder border_black"
                            id="user_description" name="user_description" rows="3"
                            readonly><?php echo htmlspecialchars($user['user_description']); ?></textarea>
                    </div>
                    <div class="text-end d-flex justify-content-end gap-3">
                        <button type="button" class="btn btn-outline-black inter-medium-25 border_black"
                            style="width:163px; height:53px;" onclick="document.getElementById('user_description').removeAttribute('readonly');">
                            Edit
                        </button>
                        <button type="submit" class="btn btn-outline-black inter-medium-25 border_black"
                            style="width:163px; height:53px;">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</Body>
<footer>
    <?php include("footer.php"); // Include the footer ?>
</footer>
<script>
document.getElementById('profileImgInput').addEventListener('change', function() {
    document.getElementById('profileImgForm').submit();
});
</script>

</html>