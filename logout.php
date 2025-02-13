<?php
session_start(); // เริ่ม session

// ลบข้อมูลทั้งหมดใน session
session_unset(); 

// ทำลาย session
session_destroy();

// เปลี่ยนเส้นทางไปยังหน้าล็อกอิน
header('Location: login.php');
exit();
?>
