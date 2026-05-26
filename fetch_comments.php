<?php
include("config.php");
session_start();

header("Content-Type: application/json");

function comments_response($status_code, $payload) {
    http_response_code($status_code);
    echo json_encode($payload);
    exit();
}

function format_fetched_comment($row) {
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
    comments_response(401, [
        "success" => false,
        "message" => "Please log in to view comments."
    ]);
}

$target_type = $_GET["target_type"] ?? "artwork";
$artwork_id = intval($_GET["artwork_id"] ?? $_GET["target_id"] ?? 0);
$since_id = intval($_GET["since_id"] ?? 0);

if ($target_type !== "artwork") {
    comments_response(400, [
        "success" => false,
        "message" => "Only artwork comments are supported by the current database schema."
    ]);
}

if ($artwork_id <= 0) {
    comments_response(400, [
        "success" => false,
        "message" => "Invalid artwork."
    ]);
}

$artwork_check = $conn->prepare("SELECT artwork_id FROM artwork WHERE artwork_id = ?");
$artwork_check->bind_param("i", $artwork_id);
$artwork_check->execute();
$artwork_result = $artwork_check->get_result();

if ($artwork_result->num_rows === 0) {
    comments_response(404, [
        "success" => false,
        "message" => "Artwork not found."
    ]);
}

$comments_stmt = $conn->prepare(
    "SELECT c.comment_id, c.artwork_id, c.user_id, c.comment_text, c.created_at, u.user_name, u.profile_image
     FROM comment c
     JOIN user u ON c.user_id = u.user_id
     WHERE c.artwork_id = ? AND c.comment_id > ?
     ORDER BY c.created_at ASC, c.comment_id ASC"
);
$comments_stmt->bind_param("ii", $artwork_id, $since_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

$comments = [];
$latest_comment_id = $since_id;

while ($row = $comments_result->fetch_assoc()) {
    $comment = format_fetched_comment($row);
    $latest_comment_id = max($latest_comment_id, $comment["comment_id"]);
    $comments[] = $comment;
}

comments_response(200, [
    "success" => true,
    "comments" => $comments,
    "latest_comment_id" => $latest_comment_id
]);
?>
