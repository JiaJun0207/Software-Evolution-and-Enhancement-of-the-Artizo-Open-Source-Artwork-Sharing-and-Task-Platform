<?php
include("config.php");
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['UID'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please log in to save tasks."
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

$input = json_decode(file_get_contents("php://input"), true);
$taskId = isset($input["task_id"]) ? intval($input["task_id"]) : intval($_POST["task_id"] ?? 0);
$uid = intval($_SESSION['UID']);

if ($taskId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid task."
    ]);
    exit();
}

$taskStmt = $conn->prepare("SELECT task_id FROM task WHERE task_id = ? LIMIT 1");
$taskStmt->bind_param("i", $taskId);
$taskStmt->execute();
$taskResult = $taskStmt->get_result();

if ($taskResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Task not found."
    ]);
    exit();
}

$savedStmt = $conn->prepare("SELECT saved_task_id FROM saved_tasks WHERE user_id = ? AND task_id = ? LIMIT 1");
$savedStmt->bind_param("ii", $uid, $taskId);
$savedStmt->execute();
$savedResult = $savedStmt->get_result();

if ($saved = $savedResult->fetch_assoc()) {
    $deleteStmt = $conn->prepare("DELETE FROM saved_tasks WHERE saved_task_id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $saved["saved_task_id"], $uid);
    $deleteStmt->execute();

    echo json_encode([
        "success" => true,
        "saved" => false,
        "message" => "Task removed from saved tasks."
    ]);
    exit();
}

$insertStmt = $conn->prepare("INSERT IGNORE INTO saved_tasks (user_id, task_id) VALUES (?, ?)");
$insertStmt->bind_param("ii", $uid, $taskId);
$insertStmt->execute();

echo json_encode([
    "success" => true,
    "saved" => true,
    "message" => "Task saved."
]);
exit();
?>
