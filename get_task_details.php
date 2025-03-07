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

// ตรวจสอบว่ามี id ที่ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบรหัสงาน']);
    exit;
}

$task_id = $_GET['id'];

// ดึงข้อมูลงานพร้อมชื่อผู้ใช้
$sql = "SELECT t.*, 
               u1.username as assigned_username,
               u2.username as created_username,
               DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') as created_at_formatted,
               DATE_FORMAT(t.due_date, '%d/%m/%Y') as due_date_formatted
        FROM tasks t
        LEFT JOIN users u1 ON t.assigned_to = u1.user_id
        LEFT JOIN users u2 ON t.created_by = u2.user_id
        WHERE t.task_id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();

if ($task = $result->fetch_assoc()) {
    // เพิ่มข้อมูลเพิ่มเติมสำหรับการแสดงผล
    $task['priority_text'] = match($task['priority']) {
        'low' => 'ต่ำ',
        'medium' => 'ปานกลาง',
        'high' => 'สูง',
        'urgent' => 'เร่งด่วน'
    };
    
    echo json_encode([
        'success' => true,
        'task' => $task
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่พบข้อมูลงาน'
    ]);
}

$stmt->close();
$mysqli->close();
?> 