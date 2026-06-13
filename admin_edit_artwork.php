<?php
include("config.php");
session_start();
include("admin_auth.php"); // Restrict to authenticated admin sessions

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

// Get artwork_id from GET
if (!isset($_GET['id'])) {
    echo "Artwork not found.";
    exit();
}
$artwork_id = intval($_GET['id']);

// Fetch artwork info
$query = "SELECT `artwork_id`, `artwork_title`, `artwork_description`, `artwork_image`, `user_id`, `category_id`, `release_at` FROM `artwork` WHERE `artwork_id` = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $artwork_id);
$stmt->execute();
$result = $stmt->get_result();
$artwork = $result->fetch_assoc();

if (!$artwork) {
    echo "Artwork not found.";
    exit();
}

// Fetch uploader info
$query_user = "SELECT `user_name`, `profile_image` FROM `user` WHERE `user_id` = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $artwork['user_id']);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$uploader = $result_user->fetch_assoc();

$uploaderImg = !empty($uploader['profile_image']) ? "assets/profile/" . $uploader['profile_image'] : "assets/profile/user_profile.png";
$uploaderName = $uploader['user_name'];

$artworkImg = !empty($artwork['artwork_image']) ? "assets/uploads/artworks/" . $artwork['artwork_image'] : "assets/uploads/artworks/default_artwork.jpeg";
$artworkTitle = $artwork['artwork_title'];
$artworkDesc = $artwork['artwork_description'];
$categoryId = $artwork['category_id'];
$releaseAt = $artwork['release_at'];

// Fetch comments for this artwork
$query_comments = "SELECT c.comment_id, c.artwork_id, c.user_id, c.comment_text, c.created_at, u.user_name, u.profile_image FROM comment c JOIN user u ON c.user_id = u.user_id WHERE c.artwork_id = ? ORDER BY c.created_at DESC";
$stmt_comments = $conn->prepare($query_comments);
$stmt_comments->bind_param("i", $artwork_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();

include("admin_navbar.php");
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artwork</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Responsive tweaks for admin_edit_artwork.php */
        @media (max-width: 991.98px) {
            .container-fluid[style*="padding-left: 60px"] {
                padding-left: 10px !important;
                padding-right: 10px !important;
            }
            .row > .col-8, .row > .col-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            .mb-5 { margin-bottom: 2rem !important; }
            .mb-4 { margin-bottom: 1rem !important; }
        }
        @media (max-width: 767.98px) {
            .inter-bold-44 { font-size: 1.5rem !important; }
            .inter-bold-24 { font-size: 1.1rem !important; }
            .inter-medium-25 { font-size: 1rem !important; }
            .category-btn-responsive {
                padding-left: 18px !important;
                padding-right: 18px !important;
                font-size: 0.95rem !important;
            }
            .mb-5 { margin-bottom: 1rem !important; }
            .mb-4 { margin-bottom: 0.75rem !important; }
            .rounded-circle {
                width: 80px !important;
                height: 80px !important;
            }
            .icon-1-1 {
                width: 23px !important;
                height: 23px !important;
                aspect-ratio: 1 / 1 !important;
                object-fit: contain !important;
                display: inline-block;
            }
        }
        @media (max-width: 425px) {
            .category-btn-responsive {
                padding-left: 8px !important;
                padding-right: 8px !important;
                font-size: 0.9rem !important;
            }
            .icon-1-1 {
                width: 18px !important;
                height: 18px !important;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid" style="padding-left: 60px; padding-right: 60px; margin-bottom: 60px;">
        <h1 class="inter-bold-44 mb-4" style="margin-top:60px;">Edit Artwork</h1>
        <div class="d-flex align-items-center mb-5">
            <div class="d-inline-block">
                <img src="<?php echo htmlspecialchars($uploaderImg); ?>" alt="Profile Image" class="rounded-circle"
                    style="width:100px; height:100px; object-fit:cover;">
            </div>
            <p class="mb-0 inter-medium-24 ms-4">
                <?php echo htmlspecialchars($uploaderName); ?>
            </p>
        </div>
        <div class="row mb-5">
            <div class="col-8">
                <p class="inter-bold-24 mb-4">Title</p>
                <form action="admin_edit_artwork_form.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="artwork_id" value="<?php echo $artwork_id; ?>">
                    <div class="d-flex mb-5 gap-3">
                        <textarea class="form-control inter-extralight-15 left-placeholder border_black"
                            id="artwork_title" name="artwork_title"
                            rows="3"><?php echo htmlspecialchars($artworkTitle); ?></textarea>
                        <button type="submit" class="btn border_black d-flex justify-content-center align-items-center"
                            style="width:53px; height:53px; padding:0;">
                            <img src="assets/icons/edit.png" alt="Edit Icon" class="icon-1-1" style="width: 23px; height: 23px;">
                        </button>
                    </div>
                </form>
                <p class="inter-bold-24 mb-4">Photo</p>
                <div class="d-flex justify-content-center align-items-center"
                    style="background-color:#f0f0f0; max-height:700px; min-height:300px;">
                    <img src="<?php echo htmlspecialchars($artworkImg); ?>" alt="artwork_image"
                        style="max-width:100%; max-height:700px; width:auto; height:auto; display:block;">
                </div>
            </div>

            <div class="col-4">
                <p class="inter-bold-24 mb-4">Category</p>
                <!-- Category change form -->
                <form action="admin_edit_artwork_form.php" method="POST" id="categoryForm">
                    <input type="hidden" name="artwork_id" value="<?php echo $artwork_id; ?>">
                    <div class="w-100 mb-3" role="group" aria-label="artwork_category">
                        <div class="d-flex gap-4 mb-4">
                            <input type="radio" class="btn-check" name="artwork_category" id="graphic_design" value="1"
                                autocomplete="off" <?php if($categoryId==1) echo 'checked'; ?> 
                                onchange="document.getElementById('categoryForm').submit();">
                            <label class="btn btn-outline-black inter-medium-25 border_black category-btn-responsive" for="graphic_design"
                                style="padding-left:53px; padding-right:53px;">Graphic Design</label>

                            <input type="radio" class="btn-check" name="artwork_category" id="3d_art" value="4"
                                autocomplete="off" <?php if($categoryId==4) echo 'checked'; ?> 
                                onchange="document.getElementById('categoryForm').submit();">
                            <label class="btn btn-outline-black inter-medium-25 border_black category-btn-responsive" for="3d_art"
                                style="padding-left:53px; padding-right:53px;">3D Art</label>
                        </div>
                        <div class="d-flex gap-4 mb-4">
                            <input type="radio" class="btn-check" name="artwork_category" id="illustration" value="2"
                                autocomplete="off" <?php if($categoryId==2) echo 'checked'; ?> 
                                onchange="document.getElementById('categoryForm').submit();">
                            <label class="btn btn-outline-black inter-medium-25 border_black category-btn-responsive" for="illustration"
                                style="padding-left:53px; padding-right:53px;">Illustration</label>

                            <input type="radio" class="btn-check" name="artwork_category" id="advertising" value="5"
                                autocomplete="off" <?php if($categoryId==5) echo 'checked'; ?> 
                                onchange="document.getElementById('categoryForm').submit();">
                            <label class="btn btn-outline-black inter-medium-25 border_black category-btn-responsive" for="advertising"
                                style="padding-left:53px; padding-right:53px;">Advertising</label>
                        </div>
                        <div class="d-flex gap-4">
                            <input type="radio" class="btn-check" name="artwork_category" id="photography" value="3"
                                autocomplete="off" <?php if($categoryId==3) echo 'checked'; ?> 
                                onchange="document.getElementById('categoryForm').submit();">
                            <label class="btn btn-outline-black inter-medium-25 border_black category-btn-responsive" for="photography"
                                style="padding-left:53px; padding-right:53px;">Photography</label>
                        </div>
                    </div>
                </form>

                <p class="inter-bold-24 mb-4">Description</p>
                <form action="admin_edit_artwork_form.php" method="POST">
                    <input type="hidden" name="artwork_id" value="<?php echo $artwork_id; ?>">
                    <div class="mb-4">
                        <textarea class="form-control inter-extralight-15 left-placeholder border_black"
                            id="artwork_description" name="artwork_description"
                            rows="6"><?php echo htmlspecialchars($artworkDesc); ?></textarea>
                    </div>
                    <div class="text-end d-flex justify-content-end gap-3">
                        <button type="submit" class="btn btn-outline-black inter-medium-25 border_black"
                            style="width:163px; height:53px;">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="inter-bold-24 mb-4">Comments</p>
        <?php while($comment = $result_comments->fetch_assoc()): 
            $commentUserImg = !empty($comment['profile_image']) ? "assets/profile/" . $comment['profile_image'] : "assets/profile/user_profile.png";
            $commentUserName = $comment['user_name'];
            $commentCreatedAt = $comment['created_at'];
            $commentText = $comment['comment_text'];
        ?>
        <div id="content_card" class="card_border mb-3" style="padding:30px;">
            <div class="d-flex align-items-center">
                <div class="flex-fill d-flex align-items-center">
                    <img src="<?php echo htmlspecialchars($commentUserImg); ?>" alt="Profile Image" class="rounded-circle"
                        style="width:50px; height:50px; object-fit:cover;">
                    <p class="card-text mb-0 inter-medium-24 ms-3">
                        <?php echo htmlspecialchars($commentUserName); ?>
                    </p>
                    <p class="card-text mb-0 inter-medium-24" style="margin-left: 100px;">
                        <?php echo htmlspecialchars($commentCreatedAt); ?>
                    </p>
                </div>
                <div class="flex-fill d-flex align-items-center justify-content-end">
                    <div class="d-flex">
                        <form method="post" action="delete_form.php" style="display:inline;">
                            <input type="hidden" name="type" value="comment">
                            <input type="hidden" name="id" value="<?php echo $comment['comment_id']; ?>">
                            <button type="submit"
                                class="btn border_black d-flex justify-content-center align-items-center"
                                style="width:53px; height:53px; padding:0;">
                                <img src="assets/icons/delete.png" alt="Delete Icon" class="icon-1-1" style="width: 23px; height: 23px;">
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <p class=" inter-medium-24 mb-0">
                    <?php echo htmlspecialchars($commentText); ?>
                </p>
            </div>
        </div>
        <?php endwhile; ?>

        <form action="delete_form.php" method="POST" class="mt-4 d-flex justify-content-center">
            <input type="hidden" name="type" value="artwork">
            <input type="hidden" name="id" value="<?php echo $artwork_id; ?>">
            <button type="submit" class="btn btn-outline-black inter-medium-25 border_black"
                style="width:266px; height:53px; margin-top: 100px;">
                Delete Post
            </button>
        </form>
    </div>
</body>

</html>