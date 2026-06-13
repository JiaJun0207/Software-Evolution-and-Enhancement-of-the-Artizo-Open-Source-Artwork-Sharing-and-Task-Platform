<?php
// Shared admin access guard. Include this at the very top of every admin-only
// page (after session_start if the page starts its own session). Normal users
// without an authenticated admin session are redirected to the admin login.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['ADMIN']) || empty($_SESSION['UID'])) {
    header("Location: admin_login.php");
    exit();
}
