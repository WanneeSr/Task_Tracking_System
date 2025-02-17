<?php
// เริ่มต้น session และเชื่อมต่อฐานข้อมูล
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

try {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
    $created_by = $_SESSION['user_id'];
    $status = 'pending';

    $sql = "INSERT INTO tasks (title, description, priority, due_date, assigned_to, created_by, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiss", $title, $description, $priority, $due_date, $assigned_to, $created_by, $status);

    if ($stmt->execute()) {
        $new_task_id = $conn->insert_id;
        
        // ดึงข้อมูลงานที่เพิ่งเพิ่ม
        $task_sql = "SELECT t.*, 
                    u.username as assigned_username,
                    c.username as created_username,
                    DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') as created_at_formatted,
                    DATE_FORMAT(t.due_date, '%d/%m/%Y') as due_date_formatted
                    FROM tasks t 
                    LEFT JOIN users u ON t.assigned_to = u.user_id
                    LEFT JOIN users c ON t.created_by = c.user_id
                    WHERE t.task_id = ?";
        
        $task_stmt = $conn->prepare($task_sql);
        $task_stmt->bind_param("i", $new_task_id);
        $task_stmt->execute();
        $task_result = $task_stmt->get_result();
        
        if ($task_result && $task_result->num_rows > 0) {
            $task = $task_result->fetch_assoc();
            
            // เพิ่มข้อมูลสำหรับการแสดงผล
            $task['priority_class'] = match($task['priority']) {
                'low' => 'success',
                'medium' => 'info',
                'high' => 'warning',
                'urgent' => 'danger'
            };
            
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
            echo json_encode(['success' => false, 'error' => 'ไม่สามารถดึงข้อมูลงานได้']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
