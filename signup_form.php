<?php
session_start(); // to start a session

if ($_SERVER["REQUEST_METHOD"] !== "POST") { //prevent users to direct access this backend page
  header("Location: signup.php");
  exit();
}

include("config.php");

$username = $_POST["user_name"];
$email = $_POST["email"];
$password = $_POST["password"];
$confirm_password = $_POST["confirm_password"];

// Prevent password mismatch
if ($password !== $confirm_password) {
  header("Location: signup.php?error=emptyfields");
  $_SESSION["feedback"] = "Passwords do not match.";
  exit();
}

// Check if any field is empty
if (empty($username) || empty($email) || empty($password)) {
  header("Location: signup.php");
  $_SESSION["feedback"] = "Please fill in all fields.";
  exit();
}

$sql = "SELECT * FROM `user` WHERE `user_name`='$username'"; // Check if username already exists
$result = $conn->query($sql);

if ($result->num_rows > 0) { // the username found in table are not allowed to register again
  header("Location: signup.php");
  $_SESSION["feedback"] = "Username already exists.";
  exit();

} else {
  echo "0 results";
  $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password for security
  $sql = "INSERT INTO `user`(`user_name`, `email`, `password`) 
VALUES ('$username','$email','$hashed_password')";

  if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
    header("location:signup.php"); // Redirect to signup page after successful registration
    $_SESSION["feedback"] = "Registration successful. You can now log in.";
  } else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
}

// Close the database connection
$conn->close();
?>