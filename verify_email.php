<?php
require_once 'db.php';
session_start();

$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // ตรวจสอบ token และเวลาหมดอายุ
    $sql = "SELECT ev.*, u.email, u.username 
            FROM email_verifications ev 
            JOIN users u ON ev.user_id = u.user_id 
            WHERE ev.token = ? AND ev.expires_at > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $verification = $result->fetch_assoc();
        
        // สร้าง token สำหรับรีเซ็ทรหัสผ่าน
        $reset_token = bin2hex(random_bytes(32));
        $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // บันทึก token รีเซ็ทรหัสผ่าน
        $sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $verification['user_id'], $reset_token, $reset_expires);
        
        if ($stmt->execute()) {
            // ลบ token ยืนยันอีเมล
            $sql = "DELETE FROM email_verifications WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $verification['id']);
            $stmt->execute();
            
            // เก็บ token รีเซ็ทรหัสผ่านใน session
            $_SESSION['reset_token'] = $reset_token;
            
            // redirect ไปยังหน้า reset_password.php
            header('Location: reset_password.php');
            exit();
        } else {
            $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง";
        }
    } else {
        $error = "ลิงก์ยืนยันไม่ถูกต้องหรือหมดอายุแล้ว กรุณาขอรีเซ็ตรหัสผ่านใหม่";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการรีเซ็ตรหัสผ่าน - ระบบติดตามงาน</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-image: 
                radial-gradient(circle at top right, rgba(67, 56, 202, 0.1), transparent 250px),
                radial-gradient(circle at bottom left, rgba(124, 58, 237, 0.1), transparent 250px);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 to-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="glass-effect rounded-3xl shadow-2xl p-8 space-y-8 border border-white/20">
                <div class="text-center space-y-2">
                    <div class="flex justify-center">
                        <div class="p-3 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">
                        ยืนยันการรีเซ็ตรหัสผ่าน
                    </h2>
                </div>

                <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                            กลับไปหน้าเข้าสู่ระบบ
                        </a>
                    </p>
                </div>
            </div>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500">
                    © <?php echo date('Y'); ?> ระบบติดตามงาน. สงวนลิขสิทธิ์.
                </p>
            </div>
        </div>
    </div>
</body>
</html> 