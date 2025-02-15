<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $role = $_POST['role'] ?? '';

    $errors = [];

    // ตรวจสอบข้อมูล
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $errors[] = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }

    if (empty($confirm_password)) {
        $errors[] = "กรุณายืนยันรหัสผ่าน";
    } elseif ($password !== $confirm_password) {
        $errors[] = "รหัสผ่านไม่ตรงกัน";
    }

    // ตรวจสอบว่า username ซ้ำหรือไม่
    $check_sql = "SELECT user_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
    }

    // ตรวจสอบว่า email ซ้ำหรือไม่
    $check_sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "อีเมลนี้ถูกใช้งานแล้ว";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            logActivity($_SESSION['user_id'], 'CREATE', "เพิ่มผู้ใช้ใหม่: $username");
            $_SESSION['success'] = "เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว";
            header("Location: users.php");
            exit();
        } else {
            $errors[] = "เกิดข้อผิดพลาด: " . $conn->error;
        }
    }
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

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <div class="invalid-feedback">
                                กรุณายืนยันรหัสผ่าน
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