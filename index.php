<?php
session_start(); // เริ่ม session

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // ถ้ายังไม่ล็อกอินให้ไปที่หน้า login
    exit();
}

echo "Welcome, " . $_SESSION['username']; // แสดงชื่อผู้ใช้
?>

<!-- เนื้อหาของหน้า index.php -->
<p>This is the home page for logged in users!</p>
<a href="logout.php">Logout</a>
