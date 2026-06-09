<?php
session_start(); // to start a session

if ($_SERVER["REQUEST_METHOD"] != "POST") { //prevent users to direct access this backend page
  header("Location: login.php");
  exit();
}

include("config.php");

$identifier = trim($_POST["user_name"] ?? "");
$password = $_POST["password"] ?? "";


// Check if any field is empty
if (empty($identifier) || empty($password)) {
  header("Location: login.php");
  $_SESSION["feedback"] = "Please fill in all fields.";
  exit();
}

$isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

if ($isEmail) {
  $stmt = $conn->prepare("SELECT user_id, user_name, password FROM `user` WHERE email = ? LIMIT 1");
  $stmt->bind_param("s", $identifier);
} else {
  $stmt = $conn->prepare("SELECT user_id, user_name, password FROM `user` WHERE BINARY user_name = ? LIMIT 1");
  $stmt->bind_param("s", $identifier);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) { // the username found in table, login here
  $row = $result->fetch_assoc();

  if (password_verify($password, $row['password'])) { // Verify the password
    //password correct go to homepage

    $_SESSION['UID'] = $row['user_id']; // Assuming 'user' is the primary key in your user table
    $_SESSION['login_success_message'] = $isEmail ? "Login successful using email." : "Login successful using username.";
    $_SESSION['login_success_redirect'] = true;

    header('location: login.php'); // Show one-time success evidence before redirecting to homepage
    exit();
  } else {
    //password incorrect
    header('Location: login.php');
    $_SESSION["feedback"] = "Incorrect password. Please try again.";
    exit();
  }
  //if username not found,go to sign up
} else {
  $_SESSION["feedback"] = "Account not found. Please sign up.";
  header("Location: signup.php");
  exit();
}



$conn->close();
?>
