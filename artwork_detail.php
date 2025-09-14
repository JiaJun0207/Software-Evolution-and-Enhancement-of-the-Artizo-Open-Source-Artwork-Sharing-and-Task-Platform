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
    <?php
    if (isset($_GET['id'])) {
        $artwork_id = $_GET['id'];
    } else {
        $artwork_id = 0; // Default or error value
    }

    // Fetch artwork and uploader info
    $sql = "SELECT a.*, u.user_name, u.profile_image, c.category_name 
            FROM artwork a
            JOIN user u ON a.user_id = u.user_id
            JOIN category c ON a.category_id = c.category_id
            WHERE a.artwork_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $artwork = $result->fetch_assoc();
        $uploaderImg = !empty($artwork['profile_image']) ? "assets/profile/" . $artwork['profile_image'] : "assets/profile/user_profile.png";
        $uploaderName = $artwork['user_name'];
        $categoryName = $artwork['category_name'];

        // Set category color based on category name
        $categoryColor = "#333"; // default color
        switch (strtolower($categoryName)) {
            case "photography":
                $categoryColor = "#B81149";
                break;
            case "graphic design":
                $categoryColor = "#FD399D";
                break;
            case "illustration":
                $categoryColor = "#033FDE";
                break;
            case "3d art":
                $categoryColor = "#F7822A";
                break;
            case "advertising":
                $categoryColor = "#8DE45B";
                break;
            // add more cases as needed
            default:
                $categoryColor = "#333";
        }

        // Fetch comments for this artwork
        $comments = [];
        $commentSql = "SELECT c.*, u.user_name, u.profile_image FROM comment c JOIN user u ON c.user_id = u.user_id WHERE c.artwork_id = ? ORDER BY c.created_at ASC";
        $commentStmt = $conn->prepare($commentSql);
        $commentStmt->bind_param("i", $artwork_id);
        $commentStmt->execute();
        $commentResult = $commentStmt->get_result();
        while ($row = $commentResult->fetch_assoc()) {
            $comments[] = $row;
        }

        // Handle comment submission (AJAX)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
            $commentText = trim($_POST['comment_text']);
            if ($commentText !== '') {
                $insertComment = $conn->prepare("INSERT INTO comment (artwork_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
                $insertComment->bind_param("iis", $artwork_id, $uid, $commentText);
                $insertComment->execute();
                echo "success";
                exit();
            }
        }
        ?>
        <div class="container-fluid px-3 px-md-5 pb-4 pb-md-5" style="margin-top:60px;">
            <div class="d-flex align-items-center flex-column flex-sm-row">
                <div class="d-inline-block mb-3 mb-sm-0">
                    <img src="<?php echo htmlspecialchars($uploaderImg); ?>" alt="Profile Image" class="rounded-circle"
                        style="width:65px; height:65px; object-fit:cover;">
                </div>
                <p class="mb-0 inter-medium-24 ms-0 ms-sm-4 mt-2 mt-sm-0 text-center text-sm-start">
                    <?php echo htmlspecialchars($uploaderName); ?>
                </p>
            </div>
            <h1 class="inter-bold-44 mb-4 mt-4 mt-md-5 text-center text-md-start">
                <?php echo htmlspecialchars($artwork['artwork_title']); ?>
            </h1>
            <div class="row mb-5 gx-0 gy-4 gy-lg-0" style="padding-left: 0;">
                <div class="col-12 col-lg-7 d-flex justify-content-center align-items-center"
                    style="background-color:#f0f0f0; max-height:700px; min-height:300px;">
                    <img src="assets/uploads/artworks/<?php echo htmlspecialchars($artwork['artwork_image']); ?>"
                        alt="artwork_image"
                        style="max-width:100%; max-height:700px; width:auto; height:auto; display:block;">
                </div>
                <div class="col-12 col-lg-5 mt-4 mt-lg-0" style="padding-left: 20px;">
                    <h5 class="inter-bold-24 mb-4">Category</h5>
                    <div class="category_box inter-medium-25 mb-4"
                        style="color: #fff; background-color: <?php echo $categoryColor; ?>;">
                        <?php echo htmlspecialchars($categoryName); ?>
                    </div>
                    <h5 class="inter-bold-24 mb-4">Description</h5>
                    <p class="inter-extralight-24"><?php echo htmlspecialchars($artwork['artwork_description']); ?></p>
                </div>
            </div>
            <hr class="my-5" style="border:none; height:2px; background-color:#D9D9D9;">
            <div class="mb-4">
                <h5 class="inter-bold-32 mb-3">Comments</h5>
                <div id="commentsSection">
                    <?php foreach ($comments as $comment): 
                        $commenterImg = !empty($comment['profile_image']) ? "assets/profile/" . $comment['profile_image'] : "assets/profile/user_profile.png";
                        $commenterName = $comment['user_name'];
                        $commenterId = $comment['user_id'];
                    ?>
                        <div class="d-flex align-items-center mb-4 flex-row comment-row-nowrap">
                            <a href="user_profile.php?uid=<?php echo urlencode($commenterId); ?>">
                                <img src="<?php echo htmlspecialchars($commenterImg); ?>" alt="Profile Image" class="rounded-circle"
                                    style="width:65px; height:65px; object-fit:cover;">
                            </a>
                            <a href="user_profile.php?uid=<?php echo urlencode($commenterId); ?>" style="text-decoration:none;">
                                <p class="mb-0 inter-medium-24 ms-4" style="color:#000;">
                                    <?php echo htmlspecialchars($commenterName); ?>
                                </p>
                            </a>
                            <p class="mb-0 inter-extralight-24 ms-5">
                                <?php echo htmlspecialchars($comment['comment_text']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                    <!-- Comment input -->
                    <div class="mb-4">
                        <textarea class="form-control inter-medium-25 left-placeholder border_black" id="commentInput"
                            name="comment" rows="3" placeholder="Add a comment" required></textarea>
                    </div>
                </div>
            </div>
        </div>
        <script>
        document.getElementById('commentInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                var commentText = this.value.trim();
                if (commentText !== '') {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.responseText === 'success') {
                            location.reload();
                        }
                    };
                    xhr.send('comment_text=' + encodeURIComponent(commentText));
                }
            }
        });
        </script>
        <?php
    } else {
        echo "Artwork not found.";
        exit();
    }
    ?>

</body>
<footer>
    <?php include("footer.php"); // Include the footer ?>
</footer>

</html>