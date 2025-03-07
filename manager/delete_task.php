<?php
require_once '../db.php';
require_once '../auth.php';
session_start();
checkManager();

if (!isset($_GET['id'])) {
    header('Location: tasks.php');
    exit();
}

$task_id = $_GET['id'];
$manager_id = $_SESSION['user_id'];

// ลบงานเฉพาะของ manager คนนี้
$sql = "DELETE FROM tasks WHERE task_id = ? AND created_by = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $task_id, $manager_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "ลบงานเรียบร้อยแล้ว";
    } else {
        $_SESSION['error'] = "ไม่พบงานที่ระบุ หรือคุณไม่มีสิทธิ์ลบงานนี้";
    }
} else {
    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $mysqli->error;
}

header('Location: tasks.php'); 