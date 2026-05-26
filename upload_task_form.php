<?php
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['UID']; // Assign before checking user existence

if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Prevent direct access to this backend page
    header("Location: upload_task.php");
    exit();
}

include("config.php"); // Include the database connection file

// Check if user_id exists in user table
$user_check = $conn->prepare("SELECT user_id FROM user WHERE user_id = ?");
$user_check->bind_param("i", $user_id);
$user_check->execute();
$user_result = $user_check->get_result();
if ($user_result->num_rows === 0) {
    header("Location: upload_task.php");
    echo "Invalid user. Please login again.";
    exit();
}

$task_title = trim($_POST["task_title"] ?? "");
$task_description = trim($_POST["task_description"] ?? "");
$task_category_id = intval($_POST["task_category_id"] ?? 0);

// Check empty field
if (empty($task_title) || empty($task_description) || $task_category_id <= 0) {
    header("Location: upload_task.php");
  echo "All fields are required.";
  exit();
}

$category_check = $conn->prepare("SELECT category_name FROM task_categories WHERE task_category_id = ?");
$category_check->bind_param("i", $task_category_id);
$category_check->execute();
$category_result = $category_check->get_result();
$task_category = $category_result->fetch_assoc();

if (!$task_category) {
    header("Location: upload_task.php");
    echo "Invalid category.";
    exit();
}

$legacy_category_id = null;
$legacy_category = $conn->prepare("SELECT category_id FROM category WHERE category_name = ? LIMIT 1");
$legacy_category->bind_param("s", $task_category["category_name"]);
$legacy_category->execute();
$legacy_result = $legacy_category->get_result();
if ($legacy_row = $legacy_result->fetch_assoc()) {
    $legacy_category_id = intval($legacy_row["category_id"]);
} else {
    $fallback_category = $conn->prepare("SELECT category_id FROM category ORDER BY category_id ASC LIMIT 1");
    $fallback_category->execute();
    $fallback_result = $fallback_category->get_result();
    if ($fallback_row = $fallback_result->fetch_assoc()) {
        $legacy_category_id = intval($fallback_row["category_id"]);
    }
}

if ($legacy_category_id === null) {
    header("Location: upload_task.php");
    echo "Legacy category setup is missing.";
    exit();
}

$task_image = time() . '_' . $_FILES["task_image"]["name"];
$path = "assets/uploads/task/" . $task_image;

if($_FILES['task_image']['size'] > 25000000) { // Check file size
    header("Location: upload_task.php");
    echo "File size exceeds the limit of 25MB.";
    exit();
}

$imagefiletype = strtolower(pathinfo($task_image, PATHINFO_EXTENSION));
if(!in_array($imagefiletype, ['jpg', 'jpeg', 'png'])) { // Check file type
    header("Location: upload_task.php");
    echo "Only JPG, JPEG & PNG files are allowed.";
    exit();
}

if (!move_uploaded_file($_FILES["task_image"]["tmp_name"], $path)) {
    header("Location: upload_task.php");
    echo "Failed to upload image file.";
    exit();
}

// accepted_user_id stores the user who accepts the task; set to NULL for new tasks (update when a user accepts)
$task_category_column_exists = false;
$column_check = $conn->prepare("SELECT COUNT(*) AS column_count FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'task' AND COLUMN_NAME = 'task_category_id'");
$column_check->execute();
$column_result = $column_check->get_result();
if ($column_row = $column_result->fetch_assoc()) {
    $task_category_column_exists = intval($column_row["column_count"]) > 0;
}

if ($task_category_column_exists) {
    $stmt = $conn->prepare("INSERT INTO `task`(`task_title`, `task_description`, `task_image`, `post_user_id`, `category_id`, `task_category_id`, `accepted_user_id`) VALUES (?, ?, ?, ?, ?, ?, NULL)");
    $stmt->bind_param("sssiii", $task_title, $task_description, $task_image, $user_id, $legacy_category_id, $task_category_id);
} else {
    $stmt = $conn->prepare("INSERT INTO `task`(`task_title`, `task_description`, `task_image`, `post_user_id`, `category_id`, `accepted_user_id`) VALUES (?, ?, ?, ?, ?, NULL)");
    $stmt->bind_param("sssii", $task_title, $task_description, $task_image, $user_id, $legacy_category_id);
}

if ($stmt->execute()) {
    echo "Task uploaded successfully.";
    $_SESSION['feedback'] = "Task uploaded successfully.";
    header("Location: index.php"); // Redirect to index page after successful upload
    exit();
} else {
    header("Location: upload_task.php");
    echo "Error uploading task.";
    $_SESSION['feedback'] = "Error uploading task.";
    exit();
}

?>
