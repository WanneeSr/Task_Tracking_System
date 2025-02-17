<?php
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// ตั้งค่า header สำหรับ JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ไม่พบ ID ผู้ใช้');
    }

    $user_id = intval($_GET['id']);
    
    // ตรวจสอบว่า ID ถูกต้อง
    if ($user_id <= 0) {
        throw new Exception('ID ผู้ใช้ไม่ถูกต้อง');
    }

    // ใช้ Prepared Statement เพื่อป้องกัน SQL Injection
    $stmt = $conn->prepare("
        SELECT user_id, username, email, role 
        FROM users 
        WHERE user_id = ?
    ");

    if (!$stmt) {
        throw new Exception('เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL');
    }

    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception('เกิดข้อผิดพลาดในการดึงข้อมูล');
    }

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        throw new Exception('ไม่พบข้อมูลผู้ใช้');
    }

    // ส่งข้อมูลกลับเป็น JSON
    echo json_encode([
        'success' => true,
        'data' => [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);

} catch (Exception $e) {
    // ส่งข้อความ error กลับเป็น JSON
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close(); 