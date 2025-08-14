<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Prevent direct access to this backend page
  header("Location: upload_artwork.php");
  exit();
}

include("config.php"); // Include the database connection file

$artwork_title = $_POST["artwork_title"];
$artwork_description = $_POST["artwork_description"];
$artwork_category = $_POST["artwork_category"];

// Check empty field
if (empty($artwork_title) || empty($artwork_description) || empty($artwork_category)) {
    header("Location: upload_artwork.php");
  echo "All fields are required.";
  exit();
}

$artwork_image = time() . '_' . $_FILES["pimg"]["name"];
$path = "uploads/artworks/" . $artwork_image;

if($_FILES['pimg']['size'] > 25000000) { // Check file size
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

move_uploaded_file($_FILES["pimg"]["tmp_name"], $path); // Move the uploaded file to the target directory

$sql = "INSERT INTO `artwork`(`artwork_title`, `artwork_description`, `artwork_image`, `category_id`) VALUES ('$artwork_title','$artwork_description','$artwork_image','$artwork_category')";

if ($conn->query($sql) === TRUE) {
    echo "Artwork uploaded successfully.";
    $_SESSION['feedback'] = "Artwork uploaded successfully.";
    header("Location: index.php"); // Redirect to index page after successful upload
} else {
    header("Location: upload_artwork.php");
    echo "Error: " . $sql . "<br>" . $conn->error;
    $_SESSION['feedback'] = "Error uploading artwork.";
}