<?php

session_start(); // to start a session
unset($_SESSION["UID"]); // Unset the user ID session variable
header("Location: login.php"); // Redirect to login page after logout
$_SESSION["feedback"] = "Log out successful."; // Set feedback message


?>