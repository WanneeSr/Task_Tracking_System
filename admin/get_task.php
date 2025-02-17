<?php
// ปิดการแสดง error ทั้งหมด
error_reporting(0);
ini_set('display_errors', 0);

// กำหนด header เป็น JSON
header('Content-Type: application/json; charset=utf-8');

// เริ่ม session ถ้ายังไม่ได้เริ่ม
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!isset($conn)) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// ตรวจสอบว่ามี task_id
if (!isset($_GET['task_id'])) {
    echo json_encode(['error' => 'No task ID provided']);
    exit;
}

try {
    $task_id = intval($_GET['task_id']);
    
    // เตรียมคำสั่ง SQL
    $sql = "SELECT t.*, u.username as assigned_username 
            FROM tasks t 
            LEFT JOIN users u ON t.assigned_to = u.user_id 
            WHERE t.task_id = ?";
            
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    
    $stmt->bind_param("i", $task_id);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }
    
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
    
    if ($task) {
        // แปลงวันที่ให้อยู่ในรูปแบบที่ถูกต้อง
        $task['due_date'] = date('Y-m-d', strtotime($task['due_date']));
        echo json_encode($task, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['error' => 'Task not found']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 