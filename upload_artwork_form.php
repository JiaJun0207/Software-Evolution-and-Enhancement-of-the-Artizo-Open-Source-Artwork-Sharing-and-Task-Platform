<?php
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['UID']; // Assign before checking user existence

if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Prevent direct access to this backend page
    header("Location: upload_artwork.php");
    exit();
}

include("config.php"); // Include the database connection file

// Check if user_id exists in user table
$user_check = $conn->query("SELECT user_id FROM user WHERE user_id = '$user_id'");
if ($user_check->num_rows === 0) {
    header("Location: upload_artwork.php");
    echo "Invalid user. Please login again.";
    exit();
}

$artwork_title = $_POST["artwork_title"];
$artwork_description = $_POST["artwork_description"];
$artwork_category = $_POST["artwork_category"];

// Check empty field
if (empty($artwork_title) || empty($artwork_description) || empty($artwork_category)) {
    header("Location: upload_artwork.php");
  echo "All fields are required.";
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

$sql = "INSERT INTO `artwork`(`artwork_title`, `artwork_description`, `artwork_image`, `user_id`, `category_id`) VALUES ('$artwork_title','$artwork_description','$artwork_image','$user_id','$artwork_category')";

if ($conn->query($sql) === TRUE) {
    echo "Artwork uploaded successfully.";
    $_SESSION['feedback'] = "Artwork uploaded successfully.";
    header("Location: index.php"); // Redirect to index page after successful upload
    exit();
} else {
    header("Location: upload_artwork.php");
    echo "Error: " . $sql . "<br>" . $conn->error;
    $_SESSION['feedback'] = "Error uploading artwork.";
    exit();
}

?>