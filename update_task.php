<?php
require_once 'db.php';
require_once 'auth.php';

// ตรวจสอบว่าเป็นการส่งข้อมูลแบบ POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'วิธีการร้องขอไม่ถูกต้อง';
    header('Location: tasks.php');
    exit();
}

// ตรวจสอบว่ามีข้อมูลที่จำเป็นครบถ้วน
if (!isset($_POST['task_id']) || !isset($_POST['title'])) {
    $_SESSION['error'] = 'ข้อมูลไม่ครบถ้วน';
    header('Location: tasks.php');
    exit();
}

$task_id = (int)$_POST['task_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    // ตรวจสอบว่างานนี้มีอยู่จริงและผู้ใช้มีสิทธิ์แก้ไข
    $check_sql = "SELECT created_by, assigned_to FROM tasks WHERE task_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $task_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'ไม่พบงานที่ระบุ';
        header('Location: tasks.php');
        exit();
    }

    $task = $result->fetch_assoc();
    
    // ตรวจสอบสิทธิ์การแก้ไข
    $can_edit = match($role) {
        'admin' => true,
        'manager' => $task['created_by'] == $user_id,
        'employee' => $task['assigned_to'] == $user_id,
        default => false
    };

    if (!$can_edit) {
        $_SESSION['error'] = 'คุณไม่มีสิทธิ์แก้ไขงานนี้';
        header('Location: tasks.php');
        exit();
    }

    // เตรียมข้อมูลสำหรับการอัพเดต
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? $task['priority'];
    $status = $_POST['status'] ?? $task['status'];
    $due_date = $_POST['due_date'] ?? $task['due_date'];
    $assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;

    // อัพเดตข้อมูล
    $update_sql = "UPDATE tasks 
                   SET title = ?, 
                       description = ?, 
                       priority = ?, 
                       status = ?, 
                       due_date = ?, 
                       assigned_to = ?,
                       updated_at = CURRENT_TIMESTAMP
                   WHERE task_id = ?";
                   
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssii", 
        $title, 
        $description, 
        $priority, 
        $status, 
        $due_date, 
        $assigned_to, 
        $task_id
    );

    if ($update_stmt->execute()) {
        $_SESSION['success'] = 'อัพเดตงานสำเร็จ';
    } else {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการอัพเดตงาน';
    }

} catch (Exception $e) {
    $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}

header('Location: tasks.php');
exit();