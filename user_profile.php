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
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid px-3 px-md-5" style="margin-top:60px;">
        <div class="card card_border mb-4">
            <div class="card-body d-flex flex-column flex-md-row align-items-center gap-3 px-3 px-md-5">
                <div class="d-inline-block mb-3 mb-md-0">
                    <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image" class="rounded-circle"
                        style="width:100px; height:100px; object-fit:cover;">
                </div>
                <p class="card-text mb-0 inter-medium-24 text-center text-md-start">
                    <?php echo htmlspecialchars($user['user_name']); ?>
                </p>
                <div class="d-flex flex-row ms-md-auto align-items-end gap-3 mt-3 mt-md-0 justify-content-center justify-content-md-end w-100 w-md-auto">
                    <a href="saved_tasks.php" class="btn btn-outline-black inter-medium-25 border_black"
                        style="width:234px; height:53px;">Saved Tasks</a>
                    <a href="edit_profile.php" class="btn btn-outline-black inter-medium-25 border_black"
                        style="width:234px; height:53px;">Edit Profile</a>
                    <a href="logout.php" style="text-decoration:none;">
                        <div class="btn form-control btn-outline-black border_black d-flex justify-content-center align-items-center aspect-1-1"
                            style="width:53px; height:53px; padding:0;">
                            <img src="assets/icons/logout.png" alt="Logout Icon" style="width: 23px; height: 23px;">
                        </div>
                    </a>
                    <a href="support.php" style="text-decoration:none;">
                        <div class="btn form-control btn-outline-black border_black d-flex justify-content-center align-items-center aspect-1-1"
                            style="width:53px; height:53px; padding:0;">
                            <img src="assets/icons/support.png" alt="Support Icon" style="width: 23px; height: 23px;">
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="card card_border mb-5">
            <div class="card-body align-items-center gap-3 px-3 px-md-5">
                <h5 class="card-title inter-bold-24">Description</h5>
                <p class="card-text inter-extralight-15">
                    <?php echo !empty($user['user_description']) ? htmlspecialchars($user['user_description']) : 'No description provided.'; ?>
                </p>
            </div>
        </div>

        <h1 class="inter-bold-24 mb-4 px-3 px-md-5">My Artwork</h1>

        <?php

        $sql = "SELECT * FROM `artwork`";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            ?>
            <div class="row row-cols-1 row-cols-art-xs-2 row-cols-sm-2 row-cols-md-4 g-4" style="padding-bottom: 60px;">
            <?php
            //output data of each row
            while ($row = $result->fetch_assoc()) {
                ?>
                    <div class="col">
                        <a href="artwork_detail.php?id=<?php echo urlencode($row['artwork_id']); ?>"
                            style="text-decoration:none;">
                            <div class="card card_artwork h-100 w-100">
                                <img src="assets/uploads/artworks/<?php echo htmlspecialchars($row['artwork_image']); ?>"
                                    alt="Artwork" class="card-img artwork-img-full">
                            </div>
                        </a>
                    </div>
                <?php
            }
            ?>
            </div>
            <?php
        } else {
            ?>
            <p class="inter-extralight-15 px-3">users don't have artworks.</p>
            <?php

        }
        ?>

    </div>

</body>
<footer>
    <?php include("footer.php"); // Include the footer ?>
</footer>

</html>
