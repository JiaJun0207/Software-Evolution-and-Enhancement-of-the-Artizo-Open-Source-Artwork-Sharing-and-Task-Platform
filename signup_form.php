<?php
session_start(); // to start a session

if ($_SERVER["REQUEST_METHOD"] !== "POST") { //prevent users to direct access this backend page
  header("Location: signup.php");
  exit();
}

include("config.php");

$username = trim($_POST["user_name"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendSignupOtpEmail($email, $otpCode)
{
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'chanjiajun321@gmail.com';
    $mail->Password = 'dhzt vlda byqz qbmn';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('chanjiajun321@gmail.com', 'Artizo');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Artizo Account Verification OTP';
    $mail->Body = "
      <h1>Artizo Account Verification</h1>
      <p>Your OTP code is:</p>
      <h2>$otpCode</h2>
      <p>This code expires in 10 minutes.</p>
    ";

    $mail->send();
    return true;
  } catch (Exception $e) {
    return false;
  }
}

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

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: signup.php");
  $_SESSION["feedback"] = "Please enter a valid email address.";
  exit();
}

if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $password)) {
  header("Location: signup.php");
  $_SESSION["feedback"] = "Password must be at least 8 characters and include both letters and numbers.";
  exit();
}

$stmt = $conn->prepare("SELECT user_id FROM `user` WHERE BINARY user_name = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) { // the username found in table are not allowed to register again
  header("Location: signup.php");
  $_SESSION["feedback"] = "Username already exists.";
  exit();

} else {
  $email_stmt = $conn->prepare("SELECT user_id FROM `user` WHERE email = ? LIMIT 1");
  $email_stmt->bind_param("s", $email);
  $email_stmt->execute();
  $email_result = $email_stmt->get_result();

  if ($email_result->num_rows > 0) {
    header("Location: signup.php");
    $_SESSION["feedback"] = "Email already exists.";
    exit();
  }

  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  $otp_code = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
  $expires_at = date("Y-m-d H:i:s", time() + 600);

  $delete_expired = $conn->prepare("DELETE FROM pending_user_otps WHERE expires_at < NOW()");
  $delete_expired->execute();

  $delete_existing = $conn->prepare("DELETE FROM pending_user_otps WHERE BINARY user_name = ? OR email = ?");
  $delete_existing->bind_param("ss", $username, $email);
  $delete_existing->execute();

  $pending_stmt = $conn->prepare("INSERT INTO pending_user_otps (user_name, email, password_hash, otp_code, expires_at) VALUES (?, ?, ?, ?, ?)");
  $pending_stmt->bind_param("sssss", $username, $email, $hashed_password, $otp_code, $expires_at);

  if ($pending_stmt->execute()) {
    $_SESSION["pending_signup_email"] = $email;
    $_SESSION["pending_signup_username"] = $username;

    if (sendSignupOtpEmail($email, $otp_code)) {
      $_SESSION["feedback"] = "Registration submitted successfully. Please check your email for the OTP.";
      $_SESSION["feedback_type"] = "success";
      header("Location: verify_otp.php");
      exit();
    }

    $cleanup_pending = $conn->prepare("DELETE FROM pending_user_otps WHERE email = ? AND BINARY user_name = ?");
    $cleanup_pending->bind_param("ss", $email, $username);
    $cleanup_pending->execute();
    unset($_SESSION["pending_signup_email"], $_SESSION["pending_signup_username"]);
    $_SESSION["feedback"] = "Failed to send OTP email. Please configure SMTP and try again.";
    header("Location: signup.php");
    exit();
  }

  header("Location: signup.php");
  $_SESSION["feedback"] = "Unable to start account verification.";
  exit();
}

// Close the database connection
$conn->close();
?>
