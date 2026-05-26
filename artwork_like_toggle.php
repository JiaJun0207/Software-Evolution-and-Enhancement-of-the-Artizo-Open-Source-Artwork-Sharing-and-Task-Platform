<?php
include("config.php");
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['UID'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please log in to like artworks."
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit();
}

$payload = json_decode(file_get_contents("php://input"), true);
$artwork_id = intval($payload["artwork_id"] ?? $_POST["artwork_id"] ?? 0);
$user_id = intval($_SESSION['UID']);

if ($artwork_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid artwork."
    ]);
    exit();
}

$artwork_check = $conn->prepare("SELECT artwork_id FROM artwork WHERE artwork_id = ?");
$artwork_check->bind_param("i", $artwork_id);
$artwork_check->execute();
$artwork_result = $artwork_check->get_result();

if ($artwork_result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Artwork not found."
    ]);
    exit();
}

$like_check = $conn->prepare("SELECT artwork_like_id FROM artwork_likes WHERE user_id = ? AND artwork_id = ?");
$like_check->bind_param("ii", $user_id, $artwork_id);
$like_check->execute();
$like_result = $like_check->get_result();

if ($like_result->num_rows > 0) {
    $delete_like = $conn->prepare("DELETE FROM artwork_likes WHERE user_id = ? AND artwork_id = ?");
    $delete_like->bind_param("ii", $user_id, $artwork_id);
    $delete_like->execute();
    $liked = false;
} else {
    $insert_like = $conn->prepare("INSERT IGNORE INTO artwork_likes (user_id, artwork_id) VALUES (?, ?)");
    $insert_like->bind_param("ii", $user_id, $artwork_id);
    $insert_like->execute();
    $liked = true;
}

$count_stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM artwork_likes WHERE artwork_id = ?");
$count_stmt->bind_param("i", $artwork_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();

echo json_encode([
    "success" => true,
    "liked" => $liked,
    "like_count" => intval($count_row["like_count"] ?? 0)
]);
exit();
?>
