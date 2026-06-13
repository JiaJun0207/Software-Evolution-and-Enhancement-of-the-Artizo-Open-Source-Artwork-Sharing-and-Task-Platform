<?php
session_start(); // to start a session

if ($_SERVER["REQUEST_METHOD"] != "POST") { //prevent users to direct access this backend page
  header("Location: admin_login.php");
  exit();
}

include("config.php");

$username = trim($_POST["user_name"] ?? "");
$password = $_POST["password"] ?? "";


// Check if any field is empty
if (empty($username) || empty($password)) {
  $_SESSION["feedback"] = "Please fill in all fields.";
  header("Location: admin_login.php");
  exit();
}

// Only an account explicitly flagged as administrator may log in here.
$stmt = $conn->prepare("SELECT user_id, password, is_admin FROM `user` WHERE BINARY user_name = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) { // the username found in table
  if (intval($row['is_admin']) !== 1) {
    // Not an admin account: refuse access and never reveal that the user exists.
    $_SESSION["feedback"] = "Invalid admin credentials.";
    header("Location: admin_login.php");
    exit();
  }

  if (password_verify($password, $row['password'])) { // Verify the password
    $_SESSION['UID'] = $row['user_id'];
    $_SESSION['ADMIN'] = 1; // Mark this session as an authenticated admin session
    header('location: admin_index.php');
    exit();
  }

  $_SESSION["feedback"] = "Incorrect password. Please try again.";
  header('Location: admin_login.php');
  exit();
}

// Username not found.
$_SESSION["feedback"] = "Invalid admin credentials.";
header("Location: admin_login.php");
exit();
?>
