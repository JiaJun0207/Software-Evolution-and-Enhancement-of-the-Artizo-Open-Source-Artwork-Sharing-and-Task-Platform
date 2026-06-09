<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: support.php");
    exit();
}

if (($_POST["action"] ?? "") !== "submit_ticket") {
    $_SESSION["feedback"] = "Invalid support request.";
    header("Location: support.php");
    exit();
}

$uid = intval($_SESSION['UID']);
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === "" || $subject === "" || $message === "") {
    $_SESSION["feedback"] = "Please complete all ticket fields.";
    header("Location: support.php");
    exit();
}

$userEmailStmt = $conn->prepare("SELECT email FROM `user` WHERE user_id = ? LIMIT 1");
$userEmailStmt->bind_param("i", $uid);
$userEmailStmt->execute();
$userEmailResult = $userEmailStmt->get_result();
$userEmailRow = $userEmailResult->fetch_assoc();

if (!$userEmailRow || strcasecmp($email, $userEmailRow["email"]) !== 0) {
    $_SESSION["feedback"] = "Please use your account email when submitting a support ticket.";
    header("Location: support.php");
    exit();
}

if (strlen($subject) > 255) {
    $_SESSION["feedback"] = "Ticket subject is too long.";
    header("Location: support.php");
    exit();
}

$ticketCode = "";
for ($attempt = 0; $attempt < 5; $attempt++) {
    $candidate = "ART-" . date("Ymd") . "-" . strtoupper(bin2hex(random_bytes(3)));
    $check = $conn->prepare("SELECT ticket_id FROM support_tickets WHERE ticket_code = ? LIMIT 1");
    $check->bind_param("s", $candidate);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $ticketCode = $candidate;
        break;
    }
}

if ($ticketCode === "") {
    $_SESSION["feedback"] = "Unable to generate ticket code.";
    header("Location: support.php");
    exit();
}

$stmt = $conn->prepare("INSERT INTO support_tickets (ticket_code, user_id, email, phone, subject, message) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sissss", $ticketCode, $uid, $email, $phone, $subject, $message);

if ($stmt->execute()) {
    $_SESSION["ticket_code"] = $ticketCode;
    header("Location: support.php");
    exit();
}

$_SESSION["feedback"] = "Unable to submit ticket.";
header("Location: support.php");
exit();
?>
