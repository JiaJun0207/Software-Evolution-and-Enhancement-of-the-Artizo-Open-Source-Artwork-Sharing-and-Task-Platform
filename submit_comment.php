<?php
include("config.php");
session_start();

header("Content-Type: application/json");

function comment_response($status_code, $payload) {
    http_response_code($status_code);
    echo json_encode($payload);
    exit();
}

function format_comment_row($row) {
    $profile_image = !empty($row["profile_image"]) ? $row["profile_image"] : "user_profile.png";

    return [
        "comment_id" => intval($row["comment_id"]),
        "artwork_id" => intval($row["artwork_id"]),
        "user_id" => intval($row["user_id"]),
        "user_name" => $row["user_name"],
        "profile_image" => $profile_image,
        "profile_image_path" => "assets/profile/" . $profile_image,
        "comment_text" => $row["comment_text"],
        "created_at" => $row["created_at"]
    ];
}

if (!isset($_SESSION["UID"])) {
    comment_response(401, [
        "success" => false,
        "message" => "Please log in to comment."
    ]);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    comment_response(405, [
        "success" => false,
        "message" => "Invalid request method."
    ]);
}

$payload = json_decode(file_get_contents("php://input"), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$target_type = $payload["target_type"] ?? "artwork";
$artwork_id = intval($payload["artwork_id"] ?? $payload["target_id"] ?? 0);
$comment_text = trim($payload["comment_text"] ?? "");
$user_id = intval($_SESSION["UID"]);

if ($target_type !== "artwork") {
    comment_response(400, [
        "success" => false,
        "message" => "Only artwork comments are supported by the current database schema."
    ]);
}

if ($artwork_id <= 0) {
    comment_response(400, [
        "success" => false,
        "message" => "Invalid artwork."
    ]);
}

if ($comment_text === "") {
    comment_response(400, [
        "success" => false,
        "message" => "Comment cannot be empty."
    ]);
}

$artwork_check = $conn->prepare("SELECT artwork_id FROM artwork WHERE artwork_id = ?");
$artwork_check->bind_param("i", $artwork_id);
$artwork_check->execute();
$artwork_result = $artwork_check->get_result();

if ($artwork_result->num_rows === 0) {
    comment_response(404, [
        "success" => false,
        "message" => "Artwork not found."
    ]);
}

$insert_comment = $conn->prepare("INSERT INTO comment (artwork_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
$insert_comment->bind_param("iis", $artwork_id, $user_id, $comment_text);

if (!$insert_comment->execute()) {
    comment_response(500, [
        "success" => false,
        "message" => "Unable to submit comment."
    ]);
}

$comment_id = $insert_comment->insert_id;
$comment_stmt = $conn->prepare(
    "SELECT c.comment_id, c.artwork_id, c.user_id, c.comment_text, c.created_at, u.user_name, u.profile_image
     FROM comment c
     JOIN user u ON c.user_id = u.user_id
     WHERE c.comment_id = ?"
);
$comment_stmt->bind_param("i", $comment_id);
$comment_stmt->execute();
$comment_result = $comment_stmt->get_result();
$comment = $comment_result->fetch_assoc();

comment_response(201, [
    "success" => true,
    "message" => "Comment submitted.",
    "comment" => format_comment_row($comment)
]);
?>
