<?php
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// ถ้าเป็นการเรียก API
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response = ['success' => false, 'message' => '', 'debug' => []];
        
        try {
            // ตรวจสอบการเชื่อมต่อฐานข้อมูล
            if ($conn->connect_error) {
                throw new Exception("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
            }

            // ตรวจสอบข้อมูลที่จำเป็น
            if (!isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['role'])) {
                throw new Exception("ข้อมูลไม่ครบถ้วน กรุณากรอกข้อมูลให้ครบทุกช่อง");
            }

            // ทำความสะอาดข้อมูล
            $username = $conn->real_escape_string(trim($_POST['username']));
            $email = $conn->real_escape_string(trim($_POST['email']));
            $password = trim($_POST['password']);
            $role = $conn->real_escape_string(trim($_POST['role']));

            // ตรวจสอบความถูกต้องของข้อมูล
            if (empty($username) || empty($email) || empty($password) || empty($role)) {
                throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
            }

            // ตรวจสอบความยาวรหัสผ่าน
            if (strlen($password) < 6) {
                throw new Exception("รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร");
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

            // ตรวจสอบว่ามีชื่อผู้ใช้หรืออีเมลนี้ในระบบแล้วหรือไม่
            $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
            $check_result = $conn->query($check_query);

            if ($check_result->num_rows > 0) {
                throw new Exception("ชื่อผู้ใช้หรืออีเมลนี้มีในระบบแล้ว");
            }

            // เข้ารหัสรหัสผ่าน
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // เพิ่มผู้ใช้ใหม่
            $insert_query = "INSERT INTO users (username, email, password, role, created_at) 
                           VALUES ('$username', '$email', '$hashed_password', '$role', NOW())";

            if (!$conn->query($insert_query)) {
                throw new Exception("ไม่สามารถเพิ่มผู้ใช้ได้: " . $conn->error);
            }

            $new_user_id = $conn->insert_id;

            // บันทึก activity log
            $admin_id = $_SESSION['user_id'];
            $log_description = "เพิ่มผู้ใช้ใหม่: $username";
            $log_query = "INSERT INTO activity_logs (user_id, action, description, created_at) 
                         VALUES ($admin_id, 'add_user', '$log_description', NOW())";
            
            if (!$conn->query($log_query)) {
                // ถ้าไม่สามารถบันทึก log ได้ ให้แสดงข้อความแจ้งเตือนแต่ไม่ถือว่าเป็นข้อผิดพลาด
                error_log("ไม่สามารถบันทึกประวัติการเพิ่มผู้ใช้ได้: " . $conn->error);
            }

            $response['success'] = true;
            $response['message'] = "เพิ่มผู้ใช้ '$username' สำเร็จ";
            $response['user'] = [
                'id' => $new_user_id,
                'username' => $username,
                'email' => $email,
                'role' => $role
            ];

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
            error_log("Error adding user: " . $e->getMessage());
        }

        echo json_encode($response);
        exit;
    }
}

// ถ้าเป็นการแสดงหน้าเว็บ
include '../header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">เพิ่มผู้ใช้ใหม่</h3>
                    <a href="users.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> กลับ
                    </a>
                </div>
                <div class="card-body">
                    <form id="addUserForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div class="invalid-feedback">
                                กรุณากรอกชื่อผู้ใช้
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">
                                กรุณากรอกอีเมลที่ถูกต้อง
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">รหัสผ่าน</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback">
                                กรุณากรอกรหัสผ่าน
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">บทบาท</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">เลือกบทบาท</option>
                                <option value="admin">ผู้ดูแลระบบ</option>
                                <option value="manager">ผู้จัดการ</option>
                                <option value="employee">พนักงาน</option>
                            </select>
                            <div class="invalid-feedback">
                                กรุณาเลือกบทบาท
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> เพิ่มผู้ใช้
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
    }

    // แสดงปุ่ม loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังเพิ่มผู้ใช้...';

    const formData = new FormData(this);
    
    fetch('add_user.php?api=1', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Response:', data);
        if (data.success) {
            // แสดงข้อความสำเร็จ
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success alert-dismissible fade show';
            successAlert.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.querySelector('.card-body').insertBefore(successAlert, this);
            
            // redirect หลังจาก 2 วินาที
            setTimeout(() => {
                window.location.href = 'users.php';
            }, 2000);
        } else {
            // แสดงข้อความผิดพลาด
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show';
            errorAlert.innerHTML = `
                ${data.message || 'เกิดข้อผิดพลาดในการเพิ่มผู้ใช้'}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.querySelector('.card-body').insertBefore(errorAlert, this);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // แสดงข้อความผิดพลาด
        const errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
        errorAlert.innerHTML = `
            เกิดข้อผิดพลาดในการเพิ่มผู้ใช้: ${error.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.querySelector('.card-body').insertBefore(errorAlert, this);
    })
    .finally(() => {
        // คืนค่าปุ่มกลับสู่สถานะปกติ
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>

<?php include '../footer.php'; ?> 