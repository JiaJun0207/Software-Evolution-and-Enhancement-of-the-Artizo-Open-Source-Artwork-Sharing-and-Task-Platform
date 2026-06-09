<?php
require 'config.php'; // Include database connection details
session_start(); // Start session to manage user authentication

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the token and passwords from the form submission
    $token = $_POST['token'];
    $password = $_POST['new_password'];
    $password_confirmation = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $password_confirmation) {
        echo '<script>alert("Passwords do not match"); window.history.back();</script>';
        exit;
    }

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $password)) {
        echo '<script>alert("Password must be at least 8 characters and include both letters and numbers."); window.history.back();</script>';
        exit;
    }

    // Validate the reset token and expiry in the database
    $query = "SELECT user_id FROM user WHERE BINARY reset_token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token); // Bind the token to the query
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Token is valid, proceed with updating the password
        $stmt->bind_result($userID);
        $stmt->fetch();
        $stmt->close();

        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Update the password in the database and clear the reset token
        $update_query = "UPDATE `user` SET `password` = ?, reset_token = NULL WHERE `user_id` = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $hashed_password, $userID);

        if ($update_stmt->execute()) {
            // Destroy all sessions to log out the user on all active devices
            session_destroy();
            echo '<script>
                    localStorage.setItem("forceLogout", "true");
                    alert("Password has been reset successfully. You will be redirected to login.");
                    window.location.href = "index.php";
                  </script>';
        } else {
            echo '<script>alert("Error updating password."); window.history.back();</script>';
        }
    } else {
        echo '<script>alert("Invalid or expired token."); window.history.back();</script>';
    }
}
?>
