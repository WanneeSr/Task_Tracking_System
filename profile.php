<?php
session_start();
require_once 'db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลผู้ใช้
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// อัพเดตโปรไฟล์
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    
    $update_sql = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    
    if ($update_stmt === false) {
        die("Error preparing update statement: " . $conn->error);
    }
    
    $update_stmt->bind_param("ssi", $username, $email, $user_id);
    
    if ($update_stmt->execute()) {
        $success = "อัพเดตข้อมูลสำเร็จ";
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $user['username'] = $username;
        $user['email'] = $email;
    } else {
        $error = "เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ | <?php echo htmlspecialchars($user['username']); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house-door"></i>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link">
                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($user['username']); ?>
                </span>
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <!-- Profile Header -->
                    <div class="card-header bg-primary text-white p-4">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white text-primary p-3 me-3">
                                <i class="bi bi-person-circle fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1"><?php echo htmlspecialchars($user['username']); ?></h4>
                                <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                                <small>สถานะ: <?php echo htmlspecialchars($user['role']); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">อีเมล</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> บันทึกการเปลี่ยนแปลง
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="d-flex justify-content-center gap-3">
                            <a href="change_password.php" class="btn btn-outline-primary">
                                <i class="bi bi-key"></i> เปลี่ยนรหัสผ่าน
                            </a>
                            <a href="settings.php" class="btn btn-outline-secondary">
                                <i class="bi bi-gear"></i> ตั้งค่า
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 