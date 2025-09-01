<?php
session_start(); // to start a session

if ($_SERVER["REQUEST_METHOD"] != "POST") { //prevent users to direct access this backend page
  header("Location: admin_login.php");
  exit();
}

include("config.php");

$username = $_POST["user_name"];
$password = $_POST["password"];


// Check if any field is empty
if (empty($username) || empty($password)) {
  header("Location: admin_login.php");
  $_SESSION["feedback"] = "Please fill in all fields.";
  exit();
}

$sql = "SELECT * FROM `user` WHERE `user_name`='$username'"; // Check if username already exists
$result = $conn->query($sql);

if ($result->num_rows > 0) { // the username found in table, login here
  $row = $result->fetch_assoc();

  if (password_verify($password, $row['password'])) { // Verify the password
    //password correct go to homepage

    $_SESSION['UID'] = $row['user_id']; // Assuming 'user' is the primary key in your user table

    header('location: admin_index.php'); // Redirect to homepage after successful login
  } else {
    //password incorrect
    header('Location: admin_login.php');
    $_SESSION["feedback"] = "Incorrect password. Please try again.";
    exit();
  }
  //if username not found,go to sign up
} else {
  $_SESSION["feedback"] = "Username not found.";
  header("Location: admin_signup.php");
  exit();
}



$conn->close();
?>