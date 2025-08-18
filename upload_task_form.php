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
$user_check = $conn->query("SELECT user_id FROM user WHERE user_id = '$user_id'");
if ($user_check->num_rows === 0) {
    header("Location: upload_task.php");
    echo "Invalid user. Please login again.";
    exit();
}

$task_title = $_POST["task_title"];
$task_description = $_POST["task_description"];
$task_category = $_POST["task_category"];

// Check empty field
if (empty($task_title) || empty($task_description) || empty($task_category)) {
    header("Location: upload_task.php");
  echo "All fields are required.";
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
$sql = "INSERT INTO `task`(`task_title`, `task_description`, `task_image`, `post_user_id`, `category_id`, `accepted_user_id`) VALUES ('$task_title','$task_description','$task_image','$user_id','$task_category', NULL)";

if ($conn->query($sql) === TRUE) {
    echo "Task uploaded successfully.";
    $_SESSION['feedback'] = "Task uploaded successfully.";
    header("Location: index.php"); // Redirect to index page after successful upload
    exit();
} else {
    header("Location: upload_task.php");
    echo "Error: " . $sql . "<br>" . $conn->error;
    $_SESSION['feedback'] = "Error uploading task.";
    exit();
}

?>