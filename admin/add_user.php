<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    try {
        // ตรวจสอบการเชื่อมต่อฐานข้อมูล
        if ($conn->connect_error) {
            throw new Exception("การเชื่อมต่อฐานข้อมูลล้มเหลว");
        }

        // ตรวจสอบข้อมูลที่ส่งมา
        if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['role'])) {
            throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
        }

        $username = $conn->real_escape_string(trim($_POST['username']));
        $email = $conn->real_escape_string(trim($_POST['email']));
        $password = trim($_POST['password']);
        $role = $conn->real_escape_string(trim($_POST['role']));

        // ตรวจสอบความยาวรหัสผ่าน
        if (strlen($password) < 6) {
            throw new Exception('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
        }

        // ตรวจสอบรูปแบบอีเมล
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('รูปแบบอีเมลไม่ถูกต้อง');
        }

        // ตรวจสอบบทบาทที่ถูกต้อง
        $allowed_roles = ['admin', 'manager', 'employee'];
        if (!in_array($role, $allowed_roles)) {
            throw new Exception('บทบาทไม่ถูกต้อง');
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

        if ($conn->query($insert_query)) {
            $new_user_id = $conn->insert_id;
            
            // บันทึก activity log
            $admin_id = $_SESSION['user_id'];
            $log_description = "เพิ่มผู้ใช้ใหม่: $username";
            $log_query = "INSERT INTO activity_logs (user_id, action, description, created_at) 
                         VALUES ($admin_id, 'add_user', '$log_description', NOW())";
            $conn->query($log_query);

            $response['success'] = true;
            $response['message'] = "เพิ่มผู้ใช้ '$username' สำเร็จ";
            $response['user'] = [
                'id' => $new_user_id,
                'username' => $username,
                'email' => $email,
                'role' => $role
            ];
        } else {
            throw new Exception("ไม่สามารถเพิ่มผู้ใช้ได้");
        }

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        error_log("Error adding user: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}
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
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   required>
                            <div class="invalid-feedback">
                                กรุณากรอกชื่อผู้ใช้
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   required>
                            <div class="invalid-feedback">
                                กรุณากรอกอีเมลที่ถูกต้อง
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">บทบาท</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">เลือกบทบาท</option>
                                <option value="manager" <?php echo (isset($_POST['role']) && $_POST['role'] === 'manager') ? 'selected' : ''; ?>>
                                    ผู้จัดการ
                                </option>
                                <option value="employee" <?php echo (isset($_POST['role']) && $_POST['role'] === 'employee') ? 'selected' : ''; ?>>
                                    พนักงาน
                                </option>
                            </select>
                            <div class="invalid-feedback">
                                กรุณาเลือกบทบาท
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">รหัสผ่าน</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback">
                                กรุณากรอกรหัสผ่าน
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
// เพิ่ม JavaScript สำหรับ form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()
</script>

<?php include '../footer.php'; ?> 