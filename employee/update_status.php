<?php
require_once '../db.php';
require_once '../auth.php';
session_start();
checkEmployee();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$task_id = $_POST['task_id'] ?? null;
$status = $_POST['status'] ?? null;
$employee_id = $_SESSION['user_id'];

if (!$task_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// ตรวจสอบว่างานนี้เป็นของพนักงานคนนี้หรือไม่
$check_sql = "SELECT assigned_to FROM tasks WHERE task_id = ?";
$stmt = $mysqli->prepare($check_sql);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();

if (!$task || $task['assigned_to'] !== $employee_id) {
    echo json_encode(['success' => false, 'message' => 'You are not authorized to update this task']);
    exit();
}

// อัพเดทสถานะ
$update_sql = "UPDATE tasks SET status = ? WHERE task_id = ? AND assigned_to = ?";
$stmt = $mysqli->prepare($update_sql);
$stmt->bind_param("sii", $status, $task_id, $employee_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $mysqli->error]);
} 