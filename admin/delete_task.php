<?php
// ปิดการแสดง errors
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

// ตรวจสอบสิทธิ์ admin
checkAdmin();

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!isset($mysqli)) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// ตรวจสอบว่าเป็น POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    // ตรวจสอบว่ามี task_id
    if (!isset($_POST['task_id'])) {
        throw new Exception('Task ID is required');
    }

    $task_id = intval($_POST['task_id']);

    // เตรียม SQL query
    $sql = "DELETE FROM tasks WHERE task_id = ?";
    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        throw new Exception($mysqli->error);
    }

    // Bind parameter
    $stmt->bind_param("i", $task_id);

    // Execute query
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    // ตรวจสอบว่ามีการลบจริง
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Task not found or already deleted'
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
} 