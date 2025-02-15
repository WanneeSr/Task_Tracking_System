<?php
$base_path = __DIR__;
require_once $base_path . '/db.php';
require_once $base_path . '/check_token.php';

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /tts-project/login.php");
        exit();
    }
    checkToken();
}

// ตรวจสอบสิทธิ์ admin
function checkAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = "กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ";
        header("Location: ../login.php");
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
    global $conn;
    
    $sql = "SELECT created_by, assigned_to FROM tasks WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
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
function canEditTask($taskId) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'manager') {
        return true;
    }
    
    global $conn;
    $sql = "SELECT created_by FROM tasks WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();

    return $task['created_by'] === $_SESSION['user_id'];
}

// ตรวจสอบสิทธิ์ในการลบงาน
function canDeleteTask($taskId) {
    return $_SESSION['role'] === 'admin' || 
           $_SESSION['role'] === 'manager' || 
           isTaskCreator($taskId);
}

// ตรวจสอบว่าเป็นเจ้าของงานหรือไม่
function isTaskCreator($taskId) {
    global $conn;
    $sql = "SELECT created_by FROM tasks WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $task = $result->fetch_assoc();

    return $task['created_by'] === $_SESSION['user_id'];
}

function checkEmployee() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
        $_SESSION['error'] = "กรุณาเข้าสู่ระบบด้วยบัญชีพนักงาน";
        header("Location: ../login.php");
        exit();
    }
}
?> 
