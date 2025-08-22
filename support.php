<?php
include("config.php");// Include the database connection file

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
    <title>Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid" style="padding-left: 60px; padding-right: 60px; margin-top:60px;">
        <div class="card card_border mb-4">
            <div class="card-body d-flex flex-row align-items-center gap-3"
                style="padding-left:40px; padding-right:40px;">
                <div class="d-inline-block">
                    <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image" class="rounded-circle"
                        style="width:100px; height:100px; object-fit:cover;">
                </div>
                <p class="card-text mb-0 inter-medium-24">
                    <?php echo htmlspecialchars($user['user_name']); ?>
                </p>
                <div class="d-flex flex-row ms-auto align-items-end gap-3">
                    <a href="edit_profile.php" class="btn btn-outline-black inter-medium-25 border_black"
                        style="width:234px; height:53px;">Edit Profile</a>
                    <a href="logout.php" style="text-decoration:none;">
                        <div class="btn form-control btn-outline-black border_black d-flex justify-content-center align-items-center"
                            style="width:53px; height:53px; padding:0;">
                            <img src="assets/icons/logout.png" alt="Logout Icon" style="width: 23px; height: 23px;">
                        </div>
                    </a>
                    <a href="support.php" style="text-decoration:none;">
                        <div class="btn form-control btn-outline-black border_black d-flex justify-content-center align-items-center"
                            style="width:53px; height:53px; padding:0;">
                            <img src="assets/icons/support.png" alt="Support Icon" style="width: 23px; height: 23px;">
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </div>

</body>
<footer>
    <?php include("footer.php"); // Include the footer ?>
</footer>

</html>