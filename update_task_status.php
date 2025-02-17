<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

// รับข้อมูล JSON จาก request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['task_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$task_id = $data['task_id'];
$status = $data['status'];
$user_id = $_SESSION['user_id'];

// ตรวจสอบสิทธิ์ในการแก้ไข
$check_permission = "SELECT * FROM tasks 
                    WHERE task_id = ? 
                    AND (created_by = ? OR assigned_to = ? OR ? IN (
                        SELECT user_id FROM users WHERE role = 'admin' OR role = 'manager'
                    ))";
$stmt = $conn->prepare($check_permission);
$stmt->bind_param("iiii", $task_id, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์แก้ไขงานนี้']);
    exit;
}

// อัพเดทสถานะงาน
$update_sql = "UPDATE tasks SET status = ?, updated_at = NOW() WHERE task_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("si", $status, $task_id);

if ($update_stmt->execute()) {
    // ดึงข้อมูลงานที่อัพเดทแล้ว
    $fetch_sql = "SELECT t.*, 
                         u1.username as assigned_username,
                         u2.username as created_username,
                         DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') as created_at_formatted,
                         DATE_FORMAT(t.due_date, '%d/%m/%Y') as due_date_formatted
                  FROM tasks t
                  LEFT JOIN users u1 ON t.assigned_to = u1.user_id
                  LEFT JOIN users u2 ON t.created_by = u2.user_id
                  WHERE t.task_id = ?";
    
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param("i", $task_id);
    $fetch_stmt->execute();
    $updated_task = $fetch_stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'อัพเดทสถานะสำเร็จ',
        'task' => $updated_task
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการอัพเดทสถานะ'
    ]);
}

$stmt->close();
$update_stmt->close();
$conn->close();
?> 