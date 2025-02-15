<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// ตรวจสอบว่ามี ID หรือไม่
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// ถ้ามีการส่งฟอร์มแก้ไข
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // ถ้ามีการเปลี่ยนรหัสผ่าน
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET 
                username = ?, 
                email = ?, 
                password = ?,
                role = ?,
                is_active = ?
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $username, $email, $password, $role, $is_active, $user_id);
    } else {
        // ถ้าไม่ได้เปลี่ยนรหัสผ่าน
        $sql = "UPDATE users SET 
                username = ?, 
                email = ?, 
                role = ?,
                is_active = ?
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $username, $email, $role, $is_active, $user_id);
    }

    if ($stmt->execute()) {
        header("Location: users.php?success=1");
        exit();
    } else {
        $error = "เกิดข้อผิดพลาดในการแก้ไขข้อมูล";
    }
}

// ดึงข้อมูลผู้ใช้
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ถ้าไม่พบผู้ใช้
if (!$user) {
    header("Location: users.php");
    exit();
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">แก้ไขข้อมูลผู้ใช้</h5>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $user_id; ?>)">
                        <i class="bi bi-trash"></i> ลบผู้ใช้
                    </button>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">ชื่อผู้ใช้</label>
                            <input type="text" class="form-control" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">อีเมล</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">รหัสผ่านใหม่ (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">บทบาท</label>
                            <select class="form-select" name="role" required>
                                <option value="employee" <?php echo $user['role'] == 'employee' ? 'selected' : ''; ?>>พนักงาน</option>
                                <option value="manager" <?php echo $user['role'] == 'manager' ? 'selected' : ''; ?>>ผู้จัดการ</option>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>ผู้ดูแลระบบ</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                       <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label">เปิดใช้งาน</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="users.php" class="btn btn-secondary">ยกเลิก</a>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(userId) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?')) {
        window.location.href = 'delete_user.php?id=' + userId;
    }
}
</script>

<?php include '../footer.php'; ?> 