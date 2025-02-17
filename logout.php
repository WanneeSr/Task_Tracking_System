<?php
session_start();

// ล้างข้อมูล session ทั้งหมด
$_SESSION = array();

// ลบ session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// ทำลาย session
session_destroy();

// redirect ไปยังหน้า login ด้วย path ที่ถูกต้อง
header('Location: /task_tracking_system/login.php');
exit();
?>