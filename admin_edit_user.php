<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

// Get user_id from GET
if (!isset($_GET['id'])) {
    echo "User not found.";
    exit();
}
$edit_user_id = intval($_GET['id']);

// Fetch user info to edit
$query = "SELECT `profile_image`, `user_name`, `user_description` FROM `user` WHERE `user_id` = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $edit_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit();
}

$profileImg = !empty($user['profile_image']) ? "assets/profile/" . $user['profile_image'] : "assets/profile/user_profile.png";

include("admin_navbar.php");
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid" style="padding-left: 60px; padding-right: 60px;">
        <h1 class="inter-bold-44 mb-5" style="margin-top:60px;">Edit User's Profile</h1>
        <div class="row">
            <div class="col-5">
                <p class="inter-medium-25 mb-4" style="padding-left:40px; padding-right:40px;">
                    <?php echo htmlspecialchars($user['user_name']); ?>
                </p>
                <div class="d-flex flex-row align-items-center gap-3 mb-3">
                    <div class="d-inline-block">
                        <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image"
                            class="rounded-circle" style="width:150px; height:150px; object-fit:cover;">
                    </div>
                    <div class="d-flex flex-row ms-auto align-items-end gap-3">
                        <!-- Profile image update form -->
                        <form action="admin_edit_user_form.php" method="POST" enctype="multipart/form-data" id="profileImgForm">
                            <input type="hidden" name="user_id" value="<?php echo $edit_user_id; ?>">
                            <input type="file" name="profile_image" accept="image/*" id="profileImgInput" style="display:none;">
                            <button type="button" class="btn btn-outline-black inter-medium-25 border_black"
                                style="width:266px; height:53px;"
                                onclick="document.getElementById('profileImgInput').click();">
                                Upload Photo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-7" style="padding-left:60px;">
                <h1 class="inter-bold-24 mt-2 mb-4">Description</h1>
                <!-- Description update form -->
                <form action="admin_edit_user_form.php" method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $edit_user_id; ?>">
                    <div class="mb-4">
                        <textarea class="form-control inter-extralight-15 left-placeholder border_black"
                            id="user_description" name="user_description"
                            rows="6"><?php echo htmlspecialchars($user['user_description']); ?></textarea>
                    </div>
                    <div class="text-end d-flex justify-content-end gap-3">
                        <button type="submit" class="btn btn-outline-black inter-medium-25 border_black"
                            style="width:163px; height:53px;">
                            Save
                        </button>
                    </div>
                </form>
                <!-- Delete account form -->
                <form action="delete_form.php" method="POST" class="mt-4">
                    <input type="hidden" name="type" value="user">
                    <input type="hidden" name="id" value="<?php echo $edit_user_id; ?>">
                    <button type="submit" class="btn btn-outline-black inter-medium-25 border_black"
                        style="width:266px; height:53px;">
                        Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Auto-submit profile image form when file selected
        document.getElementById('profileImgInput').addEventListener('change', function() {
            document.getElementById('profileImgForm').submit();
        });
    </script>
</body>

</html>