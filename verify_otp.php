<?php
session_start();
include("config.php");

$pendingEmail = $_SESSION["pending_signup_email"] ?? "";
$pendingUsername = $_SESSION["pending_signup_username"] ?? "";

if ($pendingEmail === "" || $pendingUsername === "") {
    $_SESSION["feedback"] = "Please register before entering an OTP.";
    header("Location: signup.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $otp = trim($_POST["otp_code"] ?? "");

    if (!preg_match('/^\d{6}$/', $otp)) {
        $_SESSION["feedback"] = "Please enter the 6-digit OTP.";
        header("Location: verify_otp.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT pending_id, user_name, email, password_hash, otp_code, expires_at, attempt_count FROM pending_user_otps WHERE email = ? AND BINARY user_name = ? LIMIT 1");
    $stmt->bind_param("ss", $pendingEmail, $pendingUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    $pending = $result->fetch_assoc();

    if (!$pending) {
        $_SESSION["feedback"] = "Verification session expired. Please register again.";
        header("Location: signup.php");
        exit();
    }

    if (strtotime($pending["expires_at"]) < time()) {
        $delete = $conn->prepare("DELETE FROM pending_user_otps WHERE pending_id = ?");
        $delete->bind_param("i", $pending["pending_id"]);
        $delete->execute();
        unset($_SESSION["pending_signup_email"], $_SESSION["pending_signup_username"]);
        $_SESSION["feedback"] = "OTP expired. Please register again.";
        header("Location: signup.php");
        exit();
    }

    if (intval($pending["attempt_count"]) >= 5) {
        $delete = $conn->prepare("DELETE FROM pending_user_otps WHERE pending_id = ?");
        $delete->bind_param("i", $pending["pending_id"]);
        $delete->execute();
        unset($_SESSION["pending_signup_email"], $_SESSION["pending_signup_username"]);
        $_SESSION["feedback"] = "Too many OTP attempts. Please register again.";
        header("Location: signup.php");
        exit();
    }

    if (!hash_equals($pending["otp_code"], $otp)) {
        $update = $conn->prepare("UPDATE pending_user_otps SET attempt_count = attempt_count + 1 WHERE pending_id = ?");
        $update->bind_param("i", $pending["pending_id"]);
        $update->execute();
        $_SESSION["feedback"] = "Invalid OTP.";
        header("Location: verify_otp.php");
        exit();
    }

    $userCheck = $conn->prepare("SELECT user_id FROM `user` WHERE BINARY user_name = ? OR email = ? LIMIT 1");
    $userCheck->bind_param("ss", $pending["user_name"], $pending["email"]);
    $userCheck->execute();
    $userResult = $userCheck->get_result();
    if ($userResult->num_rows > 0) {
        $_SESSION["feedback"] = "Account already exists. Please log in.";
        header("Location: login.php");
        exit();
    }

    $insert = $conn->prepare("INSERT INTO `user` (`user_name`, `email`, `password`) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $pending["user_name"], $pending["email"], $pending["password_hash"]);
    if ($insert->execute()) {
        $delete = $conn->prepare("DELETE FROM pending_user_otps WHERE pending_id = ?");
        $delete->bind_param("i", $pending["pending_id"]);
        $delete->execute();
        unset($_SESSION["pending_signup_email"], $_SESSION["pending_signup_username"]);
        $_SESSION["feedback"] = "Registration successful. You can now log in.";
        header("Location: login.php");
        exit();
    }

    $_SESSION["feedback"] = "Unable to create account.";
    header("Location: verify_otp.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row vh-100">
            <div class="col-md-6 d-flex justify-content-center align-items-center bg-white">
                <div class="text-center" style="width: 100%; max-width: 400px;">
                    <img src="assets/logo/onboarding_logo.png" alt="Logo" class="img-fluid mb-4">
                    <?php if (isset($_SESSION["feedback"])): ?>
                        <?php
                        $feedbackType = $_SESSION["feedback_type"] ?? "danger";
                        $alertClass = $feedbackType === "success" ? "alert-success" : "alert-danger";
                        ?>
                        <div id="feedback-message" class="alert <?php echo $alertClass; ?> inter-extralight-15" role="status">
                            <?php echo htmlspecialchars($_SESSION["feedback"]); ?>
                        </div>
                        <?php unset($_SESSION["feedback"], $_SESSION["feedback_type"]); ?>
                    <?php endif; ?>
                    <form action="verify_otp.php" method="post">
                        <h2 class="inter-bold-32 mb-3">Verify Account</h2>
                        <p class="inter-extralight-15 mb-3">Enter the OTP sent to your email.</p>
                        <input type="text" id="otp_code" name="otp_code" class="form-control mb-3 inter-medium-25 border_black" placeholder="6-digit OTP" maxlength="6" pattern="\d{6}" required>
                        <button type="submit" class="btn btn-outline-black w-100 mb-3 inter-medium-25 border_black">Verify</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center p-0"
                style="background: #f8f9fa;">
                <img src="assets/onboarding/image.jpg" alt="Image description"
                    style="width: 100%; height: 100vh; object-fit: cover;">
            </div>
        </div>
    </div>
</body>

</html>
