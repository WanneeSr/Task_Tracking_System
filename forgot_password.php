<?php
require_once 'db.php';
session_start();

$error = '';
$success = '';

// แก้ไขส่วนการสร้างรหัสผ่านชั่วคราว
function generateTempPassword($length = 8) {
    // กำหนดชุดตัวอักษรที่จะใช้
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $special = '@#$%';

    // รวมชุดตัวอักษรทั้งหมด
    $all = $uppercase . $lowercase . $numbers . $special;
    
    // สร้างรหัสผ่านที่มีองค์ประกอบครบทุกประเภท
    $password = [
        $uppercase[rand(0, strlen($uppercase) - 1)], // ตัวพิมพ์ใหญ่ 1 ตัว
        $lowercase[rand(0, strlen($lowercase) - 1)], // ตัวพิมพ์เล็ก 1 ตัว
        $numbers[rand(0, strlen($numbers) - 1)],     // ตัวเลข 1 ตัว
        $special[rand(0, strlen($special) - 1)]      // อักขระพิเศษ 1 ตัว
    ];
    
    // เพิ่มตัวอักษรที่เหลือแบบสุ่ม
    for ($i = count($password); $i < $length; $i++) {
        $password[] = $all[rand(0, strlen($all) - 1)];
    }
    
    // สลับตำแหน่งตัวอักษรแบบสุ่ม
    shuffle($password);
    
    return implode('', $password);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = 'กรุณากรอกอีเมล';
    } else {
        // ตรวจสอบว่ามีอีเมลนี้ในระบบหรือไม่
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // สร้างรหัสผ่านชั่วคราวแบบใหม่
            $temp_password = generateTempPassword(8);
            $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
            
            // อัพเดทรหัสผ่านชั่วคราว
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashed_password, $user['user_id']);
            
            if ($stmt->execute()) {
                $success = "รหัสผ่านชั่วคราวของคุณคือ: <strong>" . $temp_password . "</strong>" . 
                          "<br><small class='text-muted'>รหัสผ่านประกอบด้วย:</small>" .
                          "<ul class='small text-muted'>" .
                          "<li>ตัวอักษรพิมพ์ใหญ่</li>" .
                          "<li>ตัวอักษรพิมพ์เล็ก</li>" .
                          "<li>ตัวเลข</li>" .
                          "<li>อักขระพิเศษ (@#$%)</li>" .
                          "</ul>";
            } else {
                $error = 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
            }
        } else {
            $error = 'ไม่พบอีเมลนี้ในระบบ';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - TTS Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">ลืมรหัสผ่าน</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success; ?>
                                <hr>
                                <div class="alert alert-warning" role="alert">
                                    <strong>คำเตือน:</strong> กรุณาเปลี่ยนรหัสผ่านทันทีหลังจากเข้าสู่ระบบ
                                </div>
                                <a href="login.php" class="btn btn-success w-100">กลับไปหน้าเข้าสู่ระบบ</a>
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label">อีเมล</label>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           placeholder="กรอกอีเมลที่ใช้ลงทะเบียน">
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        ขอรหัสผ่านชั่วคราว
                                    </button>
                                    <a href="login.php" class="btn btn-link text-decoration-none">
                                        กลับไปหน้าเข้าสู่ระบบ
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 