<?php
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['UID']); // Assign before checking user existence

if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Prevent direct access to this backend page
    header("Location: upload_artwork.php");
    exit();
}

include("config.php"); // Include the database connection file

// Check if user_id exists in user table
$user_check = $conn->prepare("SELECT user_id FROM user WHERE user_id = ?");
$user_check->bind_param("i", $user_id);
$user_check->execute();
$user_result = $user_check->get_result();
if ($user_result->num_rows === 0) {
    header("Location: upload_artwork.php");
    echo "Invalid user. Please login again.";
    exit();
}

$artwork_title = trim($_POST["artwork_title"] ?? "");
$artwork_description = trim($_POST["artwork_description"] ?? "");
$artwork_category = intval($_POST["artwork_category"] ?? 0);

// Check empty field
if (empty($artwork_title) || empty($artwork_description) || $artwork_category <= 0) {
    header("Location: upload_artwork.php");
  echo "All fields are required.";
  exit();
}

$category_check = $conn->prepare("SELECT category_id FROM category WHERE category_id = ?");
$category_check->bind_param("i", $artwork_category);
$category_check->execute();
$category_result = $category_check->get_result();
if ($category_result->num_rows === 0) {
    header("Location: upload_artwork.php");
    echo "Invalid category.";
    exit();
}

$artwork_image = time() . '_' . $_FILES["artwork_image"]["name"];
$path = "assets/uploads/artworks/" . $artwork_image;

if($_FILES['artwork_image']['size'] > 25000000) { // Check file size
    header("Location: upload_artwork.php");
    echo "File size exceeds the limit of 25MB.";
    exit();
}

$imagefiletype = strtolower(pathinfo($artwork_image, PATHINFO_EXTENSION));
if(!in_array($imagefiletype, ['jpg', 'jpeg', 'png'])) { // Check file type
    header("Location: upload_artwork.php");
    echo "Only JPG, JPEG & PNG files are allowed.";
    exit();
}

if (!move_uploaded_file($_FILES["artwork_image"]["tmp_name"], $path)) {
    header("Location: upload_artwork.php");
    echo "Failed to upload image file.";
    exit();
}

$stmt = $conn->prepare("INSERT INTO `artwork`(`artwork_title`, `artwork_description`, `artwork_image`, `user_id`, `category_id`) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssii", $artwork_title, $artwork_description, $artwork_image, $user_id, $artwork_category);

if ($stmt->execute()) {
    echo "Artwork uploaded successfully.";
    $_SESSION['feedback'] = "Artwork uploaded successfully.";
    header("Location: index.php"); // Redirect to index page after successful upload
    exit();
} else {
    header("Location: upload_artwork.php");
    echo "Error uploading artwork.";
    $_SESSION['feedback'] = "Error uploading artwork.";
    exit();
}

?>
