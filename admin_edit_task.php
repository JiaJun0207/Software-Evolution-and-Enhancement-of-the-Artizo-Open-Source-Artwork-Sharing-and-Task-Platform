<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

// Get task_id from GET
if (!isset($_GET['id'])) {
    echo "Task not found.";
    exit();
}
$task_id = intval($_GET['id']);

// Fetch task info
$query = "SELECT `task_id`, `task_title`, `task_description`, `task_image`, `task_solution`, `task_status`, `post_user_id`, `accepted_user_id`, `category_id`, `release_at` FROM `task` WHERE `task_id` = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();

if (!$task) {
    echo "Task not found.";
    exit();
}

// Fetch poster info
$query_user = "SELECT `user_name`, `profile_image` FROM `user` WHERE `user_id` = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $task['post_user_id']);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$poster = $result_user->fetch_assoc();

$posterImg = !empty($poster['profile_image']) ? "assets/profile/" . $poster['profile_image'] : "assets/profile/user_profile.png";
$posterName = $poster['user_name'];

$taskImg = !empty($task['task_image']) ? "assets/uploads/task/" . $task['task_image'] : "assets/uploads/task/default_task.jpeg";
$taskSolutionImg = !empty($task['task_solution']) ? "assets/uploads/task_solution/" . $task['task_solution'] : null;
$taskTitle = $task['task_title'];
$taskDesc = $task['task_description'];
$taskSolution = $task['task_solution'];
$taskStatus = $task['task_status'];
$categoryId = $task['category_id'];
$releaseAt = $task['release_at'];

include("admin_navbar.php");
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Responsive tweaks for admin_edit_task.php */
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
        <h1 class="inter-bold-44 mb-4" style="margin-top:60px;">Edit Task</h1>
        <div class="d-flex align-items-center mb-5">
            <div class="d-inline-block">
                <img src="<?php echo htmlspecialchars($posterImg); ?>" alt="Profile Image" class="rounded-circle"
                    style="width:100px; height:100px; object-fit:cover;">
            </div>
            <p class="mb-0 inter-medium-24 ms-4">
                <?php echo htmlspecialchars($posterName); ?>
            </p>
        </div>
        <div class="row mb-5">
            <div class="col-8">
                <p class="inter-bold-24 mb-4">Title</p>
                <form action="admin_edit_task_form.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                    <div class="d-flex mb-5 gap-3">
                        <textarea class="form-control inter-extralight-15 left-placeholder border_black" id="task_title"
                            name="task_title" rows="3"><?php echo htmlspecialchars($taskTitle); ?></textarea>
                        <button type="submit" class="btn border_black d-flex justify-content-center align-items-center"
                            style="width:53px; height:53px; padding:0;">
                            <img src="assets/icons/edit.png" alt="Edit Icon" class="icon-1-1" style="width: 23px; height: 23px;">
                        </button>
                    </div>
                </form>
                
                <p class="inter-bold-24 mb-4">Images</p>
                <div id="taskImageSlider" class="carousel slide mb-4" data-bs-ride="carousel"
                    style="background-color:#f0f0f0; max-height:700px; min-height:300px;">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="<?php echo htmlspecialchars($taskImg); ?>" class="d-block w-100" alt="Task Image"
                                style="max-height:700px; object-fit:contain;">
                            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 rounded-3 px-3 py-2">
                                <h5 class="mb-1 text-warning">Original Task Image</h5>
                                <p class="mb-0 text-white-50">This is the image uploaded by the task poster.</p>
                            </div>
                        </div>
                        <?php if ($taskSolutionImg): ?>
                        <div class="carousel-item">
                            <img src="<?php echo htmlspecialchars($taskSolutionImg); ?>" class="d-block w-100"
                                alt="Task Solution" style="max-height:700px; object-fit:contain;">
                            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 rounded-3 px-3 py-2">
                                <h5 class="mb-1 text-success">Task Solution Image</h5>
                                <p class="mb-0 text-white-50">This is the solution image uploaded by the task accepter.</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($taskSolutionImg): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#taskImageSlider" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#taskImageSlider" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    <?php endif; ?>
                </div>

            </div>

            <div class="col-4">
                <p class="inter-bold-24 mb-4">Category</p>
                <!-- Category change form -->
                <form action="admin_edit_task_form.php" method="POST" id="categoryForm">
                    <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                    <div class="w-100 mb-3" role="group" aria-label="task_category">
                        <div class="d-flex gap-4 mb-4">
                            <input type="radio" class="btn-check" name="category_id" id="graphic_design" value="1"
                                autocomplete="off" <?php if ($categoryId == 1) echo 'checked'; ?>
                                onchange="document.getElementById('categoryForm').submit();">
                            <label class="btn btn-outline-black inter-medium-25 border_black category-btn-responsive" for="graphic_design"
                                style="padding-left:53px; padding-right:53px;">Graphic Design</label>

                            <input type="radio" class="btn-check" name="category_id" id="3d_art" value="4"
                                autocomplete="off" <?php if ($categoryId == 4) echo 'checked'; ?>
                                onchange="document.getElementById('categoryForm').submit();">
                            <label class="btn btn-outline-black inter-medium-25 border_black category-btn-responsive" for="3d_art"
                                style="padding-left:53px; padding-right:53px;">3D Art</label>
                        </div>

                        <div class="d-flex gap-4 mb-4">
                            <input type="radio" class="btn-check" name="category_id" id="illustration" value="2"
                                autocomplete="off" <?php if ($categoryId == 2) echo 'checked'; ?>
                                onchange="document.getElementById('categoryForm').submit();">
                            <label class="btn btn-outline-black inter-medium-25 border_black category-btn-responsive" for="illustration"
                                style="padding-left:53px; padding-right:53px;">Illustration</label>

                            <input type="radio" class="btn-check" name="category_id" id="advertising" value="5"
                                autocomplete="off" <?php if ($categoryId == 5) echo 'checked'; ?>
                                onchange="document.getElementById('categoryForm').submit();">
                            <label class="btn btn-outline-black inter-medium-25 border_black category-btn-responsive" for="advertising"
                                style="padding-left:53px; padding-right:53px;">Advertising</label>
                        </div>

                        <div class="d-flex gap-4">
                            <input type="radio" class="btn-check" name="category_id" id="photography" value="3"
                                autocomplete="off" <?php if ($categoryId == 3) echo 'checked'; ?>
                                onchange="document.getElementById('categoryForm').submit();">
                            <label class="btn btn-outline-black inter-medium-25 border_black category-btn-responsive" for="photography"
                                style="padding-left:53px; padding-right:53px;">Photography</label>
                        </div>
                    </div>
                </form>

                <p class="inter-bold-24 mb-4">Description</p>
                <form action="admin_edit_task_form.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                    <div class="mb-4">
                        <textarea class="form-control inter-extralight-15 left-placeholder border_black"
                            id="task_description" name="task_description"
                            rows="6"><?php echo htmlspecialchars($taskDesc); ?></textarea>
                    </div>
                    <div class="text-end d-flex justify-content-end gap-3">
                        <button type="submit" class="btn btn-outline-black inter-medium-25 border_black"
                            style="width:163px; height:53px;">
                            Save
                        </button>
                    </div>
                </form>


                <p class="inter-bold-24 mb-4">Status</p>
                <!-- Show current status badge above the select -->
                <div class="mb-2">
                    <?php
                    // Map 'accept' to 'Pending' for display
                    $displayStatus = $taskStatus;
                    if ($taskStatus == 'accept') {
                        $displayStatus = 'pending';
                    }
                    ?>
                    <?php if ($displayStatus == 'pending'): ?>
                        <span class="badge bg-warning text-dark px-3 py-2 fs-5">Current Status: Pending</span>
                    <?php elseif ($displayStatus == 'accepted'): ?>
                        <span class="badge bg-primary px-3 py-2 fs-5">Current Status: Accepted</span>
                    <?php elseif ($displayStatus == 'submitted'): ?>
                        <span class="badge bg-success px-3 py-2 fs-5">Current Status: Submitted</span>
                    <?php else: ?>
                        <span class="badge bg-secondary px-3 py-2 fs-5">Current Status: <?php echo htmlspecialchars($displayStatus); ?></span>
                    <?php endif; ?>
                </div>
                <form action="admin_edit_task_form.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                    <div class="mb-4">
                        <select class="form-select border_black" id="task_status" name="task_status">
                            <option value="accept" <?php if ($taskStatus == 'accept') echo 'selected'; ?>>Pending</option>
                            <option value="accepted" <?php if ($taskStatus == 'accepted') echo 'selected'; ?>>Accepted</option>
                            <option value="submitted" <?php if ($taskStatus == 'submitted') echo 'selected'; ?>>Submitted</option>
                        </select>
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

        <form action="delete_form.php" method="POST" class="mt-4 d-flex justify-content-center">
            <input type="hidden" name="type" value="task">
            <input type="hidden" name="id" value="<?php echo $task_id; ?>">
            <button type="submit" class="btn btn-outline-black inter-medium-25 border_black"
                style="width:266px; height:53px; margin-top: 100px;">
                Delete Task
            </button>
        </form>
    </div>
</body>

</html>