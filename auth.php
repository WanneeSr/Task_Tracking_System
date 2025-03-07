<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_path = __DIR__;
require_once $base_path . '/db.php';
require_once $base_path . '/check_token.php';

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /task_tracking_system/login.php");
        exit();
    }
    checkToken();
}

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
        echo "<script>window.location.href = '/task_tracking_system/login.php';</script>";
        exit();
    }
}

// ตรวจสอบสิทธิ์ admin
function checkAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
        echo "<script>window.location.href = '/task_tracking_system/login.php';</script>";
        exit();
    }
}

// ตรวจสอบสิทธิ์ manager
function checkManager() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
        $_SESSION['error'] = "กรุณาเข้าสู่ระบบด้วยบัญชีผู้จัดการ";
        header("Location: ../login.php");
        exit();
    }
}

// ตรวจสอบเจ้าของงานหรือผู้ที่ได้รับมอบหมาย
function checkTaskPermission($taskId) {
    global $mysqli;
    
    $sql = "SELECT created_by, assigned_to FROM tasks WHERE task_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();

    // อนุญาตให้ admin และ manager เข้าถึงได้ทุกงาน
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'manager') {
        return true;
    }

    // ตรวจสอบว่าเป็นเจ้าของงานหรือผู้ที่ได้รับมอบหมาย
    if ($task['created_by'] === $_SESSION['user_id'] || 
        $task['assigned_to'] === $_SESSION['user_id']) {
        return true;
    }

    return false;
}

// ตรวจสอบสิทธิ์ในการแก้ไขงาน
function canEditTask($task_id) {
    global $mysqli;
    $user_id = $_SESSION['user_id'] ?? 0;
    
    // ตรวจสอบว่าเป็นงานที่ได้รับมอบหมายหรือไม่
    $stmt = $mysqli->prepare("SELECT * FROM tasks WHERE task_id = ? AND assigned_to = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// ตรวจสอบสิทธิ์ในการลบงาน
function canDeleteTask($task_id) {
    global $mysqli;
    $user_id = $_SESSION['user_id'] ?? 0;
    $role = $_SESSION['role'] ?? '';
    
    // admin สามารถลบงานได้ทั้งหมด
    if ($role === 'admin') {
        return true;
    }
    
    // ผู้ใช้ทั่วไปสามารถลบงานที่ตัวเองสร้างเท่านั้น
    $stmt = $mysqli->prepare("SELECT * FROM tasks WHERE task_id = ? AND created_by = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// ตรวจสอบว่าเป็นเจ้าของงานหรือไม่
function isTaskCreator($taskId) {
    global $mysqli;
    $sql = "SELECT created_by FROM tasks WHERE task_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $task = $result->fetch_assoc();

    return $task['created_by'] === $_SESSION['user_id'];
}

function checkEmployee() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
        $_SESSION['error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
        echo "<script>window.location.href = '/task_tracking_system/login.php';</script>";
        exit();
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf_token() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
        $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
}
?> 
