<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkEmployee();

$employee_id = $_SESSION['user_id'];

// ดึงข้อมูลพนักงาน
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

// จัดการการอัพเดทข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $errors = [];

    // ตรวจสอบรหัสผ่านปัจจุบัน
    if (!empty($current_password)) {
        if (!password_verify($current_password, $employee['password'])) {
            $errors[] = "รหัสผ่านปัจจุบันไม่ถูกต้อง";
        }
        
        // ตรวจสอบรหัสผ่านใหม่
        if (empty($new_password)) {
            $errors[] = "กรุณากรอกรหัสผ่านใหม่";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "รหัสผ่านใหม่ไม่ตรงกัน";
        }
    }

    if (empty($errors)) {
        if (!empty($new_password)) {
            // อัพเดททั้งข้อมูลและรหัสผ่าน
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE user_id = ?";
            $stmt = $mysqli->prepare($update_sql);
            $stmt->bind_param("sssi", $username, $email, $hashed_password, $employee_id);
        } else {
            // อัพเดทเฉพาะข้อมูล
            $update_sql = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
            $stmt = $mysqli->prepare($update_sql);
            $stmt->bind_param("ssi", $username, $email, $employee_id);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
            header("Location: profile.php");
            exit();
        } else {
            $errors[] = "เกิดข้อผิดพลาด: " . $mysqli->error;
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">โปรไฟล์ของฉัน</h3>
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

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($employee['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                        </div>
                        <hr>
                        <h5>เปลี่ยนรหัสผ่าน</h5>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">รหัสผ่านปัจจุบัน</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">รหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 