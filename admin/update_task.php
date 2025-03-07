<?php
// ปิดการแสดง errors
error_reporting(0);
ini_set('display_errors', 0);

// ต้องกำหนด header ก่อนที่จะมี output ใดๆ
header('Content-Type: application/json; charset=utf-8');

// เริ่ม session ถ้ายังไม่ได้เริ่ม
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';

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
    // ตรวจสอบข้อมูลที่จำเป็น
    if (!isset($_POST['task_id']) || !isset($_POST['title'])) {
        throw new Exception('Missing required fields');
    }

    // เตรียมข้อมูล
    $task_id = intval($_POST['task_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;
    $status = trim($_POST['status']);
    $priority = trim($_POST['priority']);
    $due_date = trim($_POST['due_date']);

    // เตรียม SQL query
    $sql = "UPDATE tasks SET 
            title = ?, 
            description = ?, 
            assigned_to = ?, 
            status = ?, 
            priority = ?, 
            due_date = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE task_id = ?";

    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        throw new Exception($mysqli->error);
    }

    // Bind parameters
    $stmt->bind_param("ssssssi", 
        $title, 
        $description, 
        $assigned_to, 
        $status, 
        $priority, 
        $due_date, 
        $task_id
    );

    // Execute query
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    // ตรวจสอบว่ามีการอัพเดทจริง
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Task updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes made or task not found'
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
?> 