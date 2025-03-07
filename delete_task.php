<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'auth.php';

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
$user_id = $_SESSION['user_id'];

// ตรวจสอบสิทธิ์ในการลบงาน
$check_permission = "SELECT * FROM tasks WHERE task_id = ? AND (created_by = ? OR ? IN (SELECT user_id FROM users WHERE role = 'admin'))";
$stmt = $mysqli->prepare($check_permission);
$stmt->bind_param("iii", $task_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์ลบงานนี้']);
    exit;
}

// ดำเนินการลบงาน
$delete_sql = "DELETE FROM tasks WHERE task_id = ?";
$delete_stmt = $mysqli->prepare($delete_sql);
$delete_stmt->bind_param("i", $task_id);

if ($delete_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'ลบงานสำเร็จ']);
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบงาน']);
}

$stmt->close();
$delete_stmt->close();
$mysqli->close();
?>
