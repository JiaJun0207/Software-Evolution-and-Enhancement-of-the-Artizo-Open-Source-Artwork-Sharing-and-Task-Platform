<?php
include("config.php");
session_start();
include("admin_auth.php"); // Restrict to authenticated admin sessions

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
    <style>
        /* Responsive tweaks for admin_edit_user.php */
        @media (max-width: 991.98px) {
            .container-fluid[style*="padding-left: 60px"] {
                padding-left: 10px !important;
                padding-right: 10px !important;
            }

            .edit-user-row {
                flex-direction: column !important;
            }

            .edit-user-col-left,
            .edit-user-col-right {
                max-width: 100% !important;
                flex: 0 0 100% !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .edit-user-col-right {
                padding-left: 0 !important;
                margin-top: 2rem !important;
            }

            .edit-user-profile-img {
                width: 120px !important;
                height: 120px !important;
            }

            .edit-user-btn {
                width: 100% !important;
                min-width: unset !important;
                height: 45px !important;
            }

            .edit-user-desc {
                font-size: 1rem !important;
            }
        }

        @media (max-width: 767.98px) {
            .inter-bold-44 {
                font-size: 1.5rem !important;
            }

            .inter-bold-24 {
                font-size: 1.1rem !important;
            }

            .inter-medium-25 {
                font-size: 1rem !important;
            }

            .edit-user-profile-img {
                width: 100px !important;
                height: 100px !important;
            }

            .edit-user-desc {
                font-size: 0.95rem !important;
            }

            .mb-5 {
                margin-bottom: 1rem !important;
            }

            .mb-4 {
                margin-bottom: 1rem !important;
            }

            .mt-4 {
                margin-top: 1rem !important;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid" style="padding-left: 60px; padding-right: 60px;">
        <h1 class="inter-bold-44 mb-5" style="margin-top:60px;">Edit User's Profile</h1>
        <div class="row edit-user-row d-flex flex-row">
            <div class="col-12 col-lg-5 edit-user-col-left mb-4 mb-lg-0">
                <p class="inter-medium-25 mb-4 edit-user-desc" style="padding-left:40px; padding-right:40px;">
                    <?php echo htmlspecialchars($user['user_name']); ?>
                </p>
                <div class="d-flex flex-row align-items-center gap-3 mb-3 flex-wrap">
                    <div class="d-inline-block">
                        <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image"
                            class="rounded-circle edit-user-profile-img"
                            style="width:150px; height:150px; object-fit:cover;">
                    </div>
                    <div class="d-flex flex-row ms-auto align-items-end gap-3">
                        <!-- Profile image update form -->
                        <form action="admin_edit_user_form.php" method="POST" enctype="multipart/form-data" id="profileImgForm">
                            <input type="hidden" name="user_id" value="<?php echo $edit_user_id; ?>">
                            <input type="file" name="profile_image" accept="image/*" id="profileImgInput" style="display:none;">
                            <button type="button" class="btn btn-outline-black inter-medium-25 border_black edit-user-btn"
                                style="width:266px; height:53px;"
                                onclick="document.getElementById('profileImgInput').click();">
                                Upload Photo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-7 edit-user-col-right" style="padding-left:60px;">
                <h1 class="inter-bold-24 mt-2 mb-4">Description</h1>
                <!-- Description update form -->
                <form action="admin_edit_user_form.php" method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $edit_user_id; ?>">
                    <div class="mb-4">
                        <textarea class="form-control inter-extralight-15 left-placeholder border_black edit-user-desc"
                            id="user_description" name="user_description"
                            rows="6"><?php echo htmlspecialchars($user['user_description']); ?></textarea>
                    </div>
                    <div class="text-end d-flex justify-content-end gap-3">
                        <button type="submit" class="btn btn-outline-black inter-medium-25 border_black edit-user-btn"
                            style="width:163px; height:53px;">
                            Save
                        </button>
                    </div>
                </form>
                <!-- Delete account form -->
                <form action="delete_form.php" method="POST" class="mt-4">
                    <input type="hidden" name="type" value="user">
                    <input type="hidden" name="id" value="<?php echo $edit_user_id; ?>">
                    <button type="submit" class="btn btn-outline-black inter-medium-25 border_black edit-user-btn"
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