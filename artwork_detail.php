<?php
include("config.php");

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

include("navbar.php");
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artwork</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid" style="padding-left: 60px; padding-right: 60px; margin-top:60px;">
        <div class="d-flex align-items-center">
            <div class="d-inline-block"><img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image"
                    class="rounded-circle" style="width:65px; height:65px; object-fit:cover;">
            </div>
            <p class="mb-0 inter-medium-24 ms-4">
                <?php echo htmlspecialchars($user['user_name']); ?>
            </p>
        </div>
        <h1 class="inter-bold-44 mb-4" style="margin-top:60px;">Title</h1>
        <div class="row">
            <div class="col">
                <img src="assets/uploads/artworks/default_artwork.jpeg" alt="Artwork">
            </div>
            <div class="col">
                <h5 class="inter-bold-24 mb-4">Category</h5>
                <div class="category_box inter-medium-25 mb-4" style="color: #fff;">Photography</div>
                <h5 class="inter-bold-24 mb-4">Description</h5>
                <p class="inter-extralight-24">This is a beautiful photograph capturing the essence of nature.</p>
            </div>
        </div>
        
        </div>
</body>
<footer>
    <?php include("footer.php"); // Include the footer ?>
</footer>
</html>