<?php
require_once '../db.php';
require_once '../auth.php';
session_start();
checkManager();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tasks.php');
    exit();
}

$title = trim($_POST['title']);
$description = trim($_POST['description']);
$assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
$priority = $_POST['priority'];
$due_date = $_POST['due_date'];
$created_by = $_SESSION['user_id'];
$status = 'pending';

if (empty($title) || empty($due_date)) {
    $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
    header('Location: tasks.php');
    exit();
}

$sql = "INSERT INTO tasks (title, description, assigned_to, priority, due_date, created_by, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ssissis", $title, $description, $assigned_to, $priority, $due_date, $created_by, $status);

if ($stmt->execute()) {
    $_SESSION['success'] = "เพิ่มงานเรียบร้อยแล้ว";
} else {
    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $mysqli->error;
}

header('Location: tasks.php'); 