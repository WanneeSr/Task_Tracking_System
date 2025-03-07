<?php
$base_path = __DIR__;
require_once $base_path . '/db.php';

function checkToken() {
    global $mysqli;
    
    if (!isset($_SESSION['token'])) {
        header("Location: /task_tracking_system/login.php");
        exit();
    }

    $token = $_SESSION['token'];
    $current_time = date('Y-m-d H:i:s');

    // ตรวจสอบ token ในฐานข้อมูล
    $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE token = ? AND token_expires_at > ?");
    $stmt->bind_param("ss", $token, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // ถ้า token ไม่ถูกต้องหรือหมดอายุ
        session_destroy();
        header("Location: /task_tracking_system/login.php");
        exit();
    }

    // อัพเดทเวลาหมดอายุของ token
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 day'));
    $stmt = $mysqli->prepare("UPDATE users SET token_expires_at = ? WHERE token = ?");
    $stmt->bind_param("ss", $expires_at, $token);
    $stmt->execute();
}
?>