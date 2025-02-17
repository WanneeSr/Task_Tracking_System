<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ไม่พบ ID ของงาน']);
    exit();
}

$task_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    // ดึงข้อมูลงาน
    $sql = "SELECT t.*, 
            u.username as assigned_username,
            c.username as created_username,
            DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') as created_at_formatted,
            DATE_FORMAT(t.due_date, '%d/%m/%Y') as due_date_formatted
            FROM tasks t 
            LEFT JOIN users u ON t.assigned_to = u.user_id
            LEFT JOIN users c ON t.created_by = c.user_id
            WHERE t.task_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'ไม่พบงานที่ระบุ']);
        exit();
    }

    $task = $result->fetch_assoc();
    
    // ตรวจสอบสิทธิ์การเข้าถึง
    $can_access = match($role) {
        'admin' => true,
        'manager' => $task['created_by'] == $user_id,
        'employee' => $task['assigned_to'] == $user_id,
        default => false
    };

    if (!$can_access) {
        http_response_code(403);
        echo json_encode(['error' => 'คุณไม่มีสิทธิ์ดูงานนี้']);
        exit();
    }

    // แปลงวันที่ให้อยู่ในรูปแบบที่ต้องการ
    $task['due_date'] = date('Y-m-d', strtotime($task['due_date']));
    
    echo json_encode($task);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}