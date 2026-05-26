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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    if (isset($_GET['id'])) {
        $artwork_id = intval($_GET['id']);
    } else {
        $artwork_id = 0; // Default or error value
    }

    // Fetch artwork and uploader info
    $sql = "SELECT a.*, u.user_name, u.profile_image, c.category_name,
                   COALESCE(lc.like_count, 0) AS like_count,
                   CASE WHEN ul.artwork_like_id IS NULL THEN 0 ELSE 1 END AS is_liked
            FROM artwork a
            JOIN user u ON a.user_id = u.user_id
            JOIN category c ON a.category_id = c.category_id
            LEFT JOIN (
                SELECT artwork_id, COUNT(*) AS like_count
                FROM artwork_likes
                GROUP BY artwork_id
            ) lc ON a.artwork_id = lc.artwork_id
            LEFT JOIN artwork_likes ul ON a.artwork_id = ul.artwork_id AND ul.user_id = ?
            WHERE a.artwork_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $uid, $artwork_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $artwork = $result->fetch_assoc();
        $uploaderImg = !empty($artwork['profile_image']) ? "assets/profile/" . $artwork['profile_image'] : "assets/profile/user_profile.png";
        $uploaderName = $artwork['user_name'];
        $categoryName = $artwork['category_name'];
        $likeCount = intval($artwork['like_count']);
        $isLiked = intval($artwork['is_liked']) === 1;

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
            <button type="button"
                class="btn btn-outline-black border_black artwork-like-btn artwork-like-btn-detail mb-4 <?php echo $isLiked ? 'liked' : ''; ?>"
                data-artwork-id="<?php echo htmlspecialchars($artwork_id); ?>"
                data-liked="<?php echo $isLiked ? '1' : '0'; ?>"
                aria-pressed="<?php echo $isLiked ? 'true' : 'false'; ?>">
                <i class="<?php echo $isLiked ? 'fa-solid' : 'fa-regular'; ?> fa-heart" aria-hidden="true"></i>
                <span class="artwork-like-label"><?php echo $isLiked ? 'Unlike' : 'Like'; ?></span>
                <span class="artwork-like-count"><?php echo $likeCount; ?></span>
            </button>
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
                        <div class="d-flex align-items-center mb-4 flex-row comment-row-nowrap"
                            data-comment-id="<?php echo htmlspecialchars($comment['comment_id']); ?>">
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
                </div>
                <form id="commentForm" class="mb-4">
                    <input type="hidden" id="commentArtworkId" value="<?php echo htmlspecialchars($artwork_id); ?>">
                    <div class="mb-3">
                        <textarea class="form-control inter-medium-25 left-placeholder border_black" id="commentInput"
                            name="comment" rows="3" placeholder="Add a comment" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-black border_black inter-medium-24 comment-submit-btn">
                        Submit Comment
                    </button>
                </form>
            </div>
        </div>
        <script>
        const commentsSection = document.getElementById('commentsSection');
        const commentForm = document.getElementById('commentForm');
        const commentInput = document.getElementById('commentInput');
        const commentArtworkId = document.getElementById('commentArtworkId').value;
        const renderedCommentIds = new Set();
        let latestCommentId = 0;

        commentsSection.querySelectorAll('[data-comment-id]').forEach(function (commentNode) {
            const commentId = Number(commentNode.dataset.commentId);
            if (commentId > 0) {
                renderedCommentIds.add(commentId);
                latestCommentId = Math.max(latestCommentId, commentId);
            }
        });

        function appendComment(comment) {
            const commentId = Number(comment.comment_id);
            if (!commentId || renderedCommentIds.has(commentId)) {
                return;
            }

            renderedCommentIds.add(commentId);
            latestCommentId = Math.max(latestCommentId, commentId);

            const row = document.createElement('div');
            row.className = 'd-flex align-items-center mb-4 flex-row comment-row-nowrap';
            row.dataset.commentId = String(commentId);

            const profileLink = document.createElement('a');
            profileLink.href = 'user_profile.php?uid=' + encodeURIComponent(comment.user_id);

            const profileImage = document.createElement('img');
            profileImage.src = comment.profile_image_path || 'assets/profile/user_profile.png';
            profileImage.alt = 'Profile Image';
            profileImage.className = 'rounded-circle';
            profileImage.style.width = '65px';
            profileImage.style.height = '65px';
            profileImage.style.objectFit = 'cover';
            profileLink.appendChild(profileImage);

            const nameLink = document.createElement('a');
            nameLink.href = 'user_profile.php?uid=' + encodeURIComponent(comment.user_id);
            nameLink.style.textDecoration = 'none';

            const name = document.createElement('p');
            name.className = 'mb-0 inter-medium-24 ms-4';
            name.style.color = '#000';
            name.textContent = comment.user_name;
            nameLink.appendChild(name);

            const text = document.createElement('p');
            text.className = 'mb-0 inter-extralight-24 ms-5';
            text.textContent = comment.comment_text;

            row.appendChild(profileLink);
            row.appendChild(nameLink);
            row.appendChild(text);
            commentsSection.appendChild(row);
        }

        async function fetchNewComments() {
            const params = new URLSearchParams({
                target_type: 'artwork',
                artwork_id: commentArtworkId,
                since_id: String(latestCommentId)
            });

            const response = await fetch('fetch_comments.php?' + params.toString(), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                if (response.status === 401) {
                    window.location.href = 'login.php';
                    return;
                }
                throw new Error(data.message || 'Unable to fetch comments.');
            }

            data.comments.forEach(appendComment);
        }

        commentForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const commentText = commentInput.value.trim();

            if (commentText === '') {
                return;
            }

            const submitButton = commentForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            try {
                const response = await fetch('submit_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        target_type: 'artwork',
                        artwork_id: commentArtworkId,
                        comment_text: commentText
                    })
                });
                const data = await response.json();

                if (!response.ok || !data.success) {
                    if (response.status === 401) {
                        window.location.href = 'login.php';
                        return;
                    }
                    throw new Error(data.message || 'Unable to submit comment.');
                }

                appendComment(data.comment);
                commentInput.value = '';
            } catch (error) {
                alert(error.message);
            } finally {
                submitButton.disabled = false;
            }
        });

        commentInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                if (commentForm.requestSubmit) {
                    commentForm.requestSubmit();
                } else {
                    commentForm.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }
        });

        setInterval(function () {
            fetchNewComments().catch(function () {
                // Keep polling quiet so temporary network issues do not interrupt reading.
            });
        }, 5000);
        </script>
        <script src="artwork_like.js"></script>
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
