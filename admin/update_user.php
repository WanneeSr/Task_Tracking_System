<?php
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// ตั้งค่า header สำหรับ JSON response
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        // ตรวจสอบการเชื่อมต่อฐานข้อมูล
        if ($conn->connect_error) {
            throw new Exception("การเชื่อมต่อฐานข้อมูลล้มเหลว");
        }

        // ตรวจสอบข้อมูลที่จำเป็น
        if (!isset($_POST['user_id']) || !isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['role'])) {
            throw new Exception("ข้อมูลไม่ครบถ้วน");
        }

        $user_id = intval($_POST['user_id']);
        $username = $conn->real_escape_string(trim($_POST['username']));
        $email = $conn->real_escape_string(trim($_POST['email']));
        $role = $conn->real_escape_string(trim($_POST['role']));
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        // ตรวจสอบความถูกต้องของข้อมูล
        if (empty($username) || empty($email) || empty($role)) {
            throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
        }

        // ตรวจสอบรูปแบบอีเมล
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("รูปแบบอีเมลไม่ถูกต้อง");
        }

        // ตรวจสอบบทบาทที่ถูกต้อง
        $allowed_roles = ['admin', 'manager', 'employee'];
        if (!in_array($role, $allowed_roles)) {
            throw new Exception("บทบาทไม่ถูกต้อง");
        }

        // ตรวจสอบว่ามีชื่อผู้ใช้หรืออีเมลนี้ในระบบแล้วหรือไม่ (ยกเว้นผู้ใช้ปัจจุบัน)
        $check_query = "SELECT * FROM users WHERE (username = '$username' OR email = '$email') AND user_id != $user_id";
        $check_result = $conn->query($check_query);

        if ($check_result->num_rows > 0) {
            throw new Exception("ชื่อผู้ใช้หรืออีเมลนี้มีในระบบแล้ว");
        }

        // เริ่ม transaction
        $conn->begin_transaction();

        try {
            // อัปเดตข้อมูลผู้ใช้
            if (!empty($password)) {
                // ถ้ามีการเปลี่ยนรหัสผ่าน
                if (strlen($password) < 6) {
                    throw new Exception("รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร");
                }
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET username = '$username', email = '$email', 
                               role = '$role', password = '$hashed_password' 
                               WHERE user_id = $user_id";
            } else {
                // ถ้าไม่มีการเปลี่ยนรหัสผ่าน
                $update_query = "UPDATE users SET username = '$username', email = '$email', 
                               role = '$role' WHERE user_id = $user_id";
            }

            if (!$conn->query($update_query)) {
                throw new Exception("ไม่สามารถอัปเดตข้อมูลผู้ใช้ได้");
            }

            // บันทึก activity log
            $admin_id = $_SESSION['user_id'];
            $log_description = "อัปเดตข้อมูลผู้ใช้ ID: $user_id";
            $log_query = "INSERT INTO activity_logs (user_id, action, description, created_at) 
                         VALUES ($admin_id, 'update_user', '$log_description', NOW())";
            
            if (!$conn->query($log_query)) {
                throw new Exception("ไม่สามารถบันทึกประวัติการอัปเดตได้");
            }

            // ยืนยัน transaction
            $conn->commit();

            $response['success'] = true;
            $response['message'] = "อัปเดตข้อมูลผู้ใช้สำเร็จ";

        } catch (Exception $e) {
            // ถ้าเกิดข้อผิดพลาด ให้ rollback การทำงานทั้งหมด
            $conn->rollback();
            throw $e;
        }

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        error_log("Error updating user: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}

// ถ้าไม่ใช่ POST request
echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
]);
?> 