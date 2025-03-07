<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';

// ตรวจสอบการเชื่อมต่อ
if ($mysqli->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $mysqli->connect_error);
}

// ตรวจสอบว่าตาราง users มีอยู่หรือไม่
$check_users = $mysqli->query("SHOW TABLES LIKE 'users'");
if ($check_users->num_rows == 0) {
    die("ตาราง users ไม่มีอยู่ในฐานข้อมูล กรุณาสร้างตาราง users ก่อน");
}

// ลบตารางเก่าถ้ามี
$mysqli->query("DROP TABLE IF EXISTS email_verifications");
$mysqli->query("DROP TABLE IF EXISTS password_resets");

// สร้างตาราง email_verifications
$sql = "CREATE TABLE email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($mysqli->query($sql) === TRUE) {
    echo "ตาราง email_verifications ถูกสร้างเรียบร้อยแล้ว<br>";
} else {
    echo "เกิดข้อผิดพลาดในการสร้างตาราง email_verifications: " . $mysqli->error . "<br>";
}

// สร้างตาราง password_resets
$sql = "CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($mysqli->query($sql) === TRUE) {
    echo "ตาราง password_resets ถูกสร้างเรียบร้อยแล้ว<br>";
} else {
    echo "เกิดข้อผิดพลาดในการสร้างตาราง password_resets: " . $mysqli->error . "<br>";
}

// ตรวจสอบว่าตารางถูกสร้างจริง
$check_email_verifications = $mysqli->query("SHOW TABLES LIKE 'email_verifications'");
$check_password_resets = $mysqli->query("SHOW TABLES LIKE 'password_resets'");

echo "<br>สถานะการสร้างตาราง:<br>";
echo "email_verifications: " . ($check_email_verifications->num_rows > 0 ? "มีอยู่" : "ไม่มี") . "<br>";
echo "password_resets: " . ($check_password_resets->num_rows > 0 ? "มีอยู่" : "ไม่มี") . "<br>";

$mysqli->close();
?> 