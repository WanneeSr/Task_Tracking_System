<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
require_once 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

// ถ้ามีการล็อกอินอยู่แล้ว ให้ไปที่หน้า index.php
if (isset($_SESSION['user_id'])) {
    header('Location: /task_tracking_system/index.php');
    exit();
}

// ถ้ามีการ submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $errors = [];

    // ตรวจสอบอีเมล
    if (empty($email)) {
        $errors[] = "กรุณากรอกอีเมล";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "รูปแบบอีเมลไม่ถูกต้อง";
    }

    // ตรวจสอบว่ามีอีเมลนี้ในระบบหรือไม่
    if (empty($errors)) {
        $sql = "SELECT user_id, username, email FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $errors[] = "ไม่พบอีเมลนี้ในระบบ";
        } else {
            $user = $result->fetch_assoc();
            
            // สร้าง token สำหรับรีเซ็ตรหัสผ่าน
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // บันทึก token ลงฐานข้อมูล
            $sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $user['user_id'], $token, $expires_at);
            
            if ($stmt->execute()) {
                // ส่งอีเมลพร้อมลิงก์รีเซ็ตรหัสผ่าน
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/Task_Tracking_System/reset_password.php?token=" . $token;
                
                // ใช้ PHPMailer
                $mail = new PHPMailer(true);

                try {
                    //Server settings
                    $mail->SMTPDebug = 2;
                    $mail->Debugoutput = function($str, $level) {
                        file_put_contents('mail_debug.log', date('Y-m-d H:i:s') . " - $str\n", FILE_APPEND);
                    };
                    
                    // ตั้งค่า SMTP
                    $mail->isSMTP();
                    $mail->Mailer = 'smtp';
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'mintaken77@gmail.com';
                    $mail->Password = 'brtq bsjd mizi qqju';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
                    $mail->CharSet = 'UTF-8';
                    
                    // ตั้งค่า SSL
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => true,
                            'verify_peer_name' => true,
                            'allow_self_signed' => false,
                            'verify_depth' => 3
                        )
                    );

                    // ตั้งค่า PHP
                    ini_set('SMTP', 'smtp.gmail.com');
                    ini_set('smtp_port', 465);
                    ini_set('sendmail_from', 'mintaken77@gmail.com');
                    ini_set('sendmail_path', '');

                    // ตั้งค่าเพิ่มเติม
                    $mail->Timeout = 60;
                    $mail->SMTPKeepAlive = true;

                    //Recipients
                    $mail->setFrom('mintaken77@gmail.com', 'ระบบติดตามงาน');
                    $mail->addAddress($user['email'], $user['username']);

                    //Content
                    $mail->isHTML(true);
                    $mail->Subject = 'รีเซ็ตรหัสผ่าน - ระบบติดตามงาน';
                    $mail->Body = "
                    <div style='font-family: Prompt, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #4F46E5; text-align: center;'>รีเซ็ตรหัสผ่าน</h2>
                        <p>สวัสดี {$user['username']},</p>
                        <p>คุณได้ขอรีเซ็ตรหัสผ่านสำหรับบัญชีของคุณในระบบติดตามงาน</p>
                        <p>กรุณาคลิกที่ลิงก์ด้านล่างเพื่อตั้งรหัสผ่านใหม่:</p>
                        <p style='text-align: center; margin: 30px 0;'>
                            <a href='{$reset_link}' 
                               style='background: linear-gradient(to right, #4F46E5, #7C3AED);
                                      color: white;
                                      padding: 12px 24px;
                                      text-decoration: none;
                                      border-radius: 8px;
                                      display: inline-block;'>
                                ตั้งรหัสผ่านใหม่
                            </a>
                        </p>
                        <p>ลิงก์นี้จะหมดอายุใน 1 ชั่วโมง</p>
                        <p>หากคุณไม่ได้ขอรีเซ็ตรหัสผ่าน กรุณาละเว้นอีเมลนี้</p>
                        <hr style='border: none; border-top: 1px solid #E5E7EB; margin: 20px 0;'>
                        <p style='color: #6B7280; font-size: 14px; text-align: center;'>
                            อีเมลนี้ถูกส่งโดยอัตโนมัติ กรุณาอย่าตอบกลับ
                        </p>
                    </div>";

                    // เก็บ debug output
                    ob_start();
                    $mail->send();
                    $debug_output = ob_get_clean();
                    
                    // บันทึก debug output ลงในไฟล์
                    file_put_contents('mail_debug.log', date('Y-m-d H:i:s') . "\n" . $debug_output . "\n\n", FILE_APPEND);
                    
                    // แสดงข้อความสำเร็จ
                    $success = "ส่งลิงก์รีเซ็ตรหัสผ่านไปยังอีเมลของคุณแล้ว กรุณาตรวจสอบกล่องจดหมาย";
                    header('Location: login.php');
                    exit();
                } catch (Exception $e) {
                    $errors[] = "เกิดข้อผิดพลาดในการส่งอีเมล: " . $mail->ErrorInfo;
                    $errors[] = "รายละเอียดเพิ่มเติม: " . $e->getMessage();
                    // บันทึกข้อผิดพลาดลงในไฟล์
                    file_put_contents('mail_error.log', date('Y-m-d H:i:s') . "\n" . $e->getMessage() . "\n" . $mail->ErrorInfo . "\n\n", FILE_APPEND);
                }
            } else {
                $errors[] = "เกิดข้อผิดพลาดในการบันทึก token: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - ระบบติดตามงาน</title>
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
        <!-- Main Container -->
        <div class="w-full max-w-md">
            <!-- Forgot Password Card -->
            <div class="glass-effect rounded-3xl shadow-2xl p-8 space-y-8 border border-white/20">
                <!-- Logo & Title -->
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
                        ลืมรหัสผ่าน
                    </h2>
                    <p class="text-gray-500">กรอกอีเมลของคุณเพื่อรีเซ็ตรหัสผ่าน</p>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="animate-bounce-in">
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
                                <ul class="list-disc list-inside text-sm text-red-700">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                <div class="animate-bounce-in">
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M5 13l4 4L19 7">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">
                                    <?php echo htmlspecialchars($success); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Forgot Password Form -->
                <form method="POST" class="space-y-6">
                    <!-- Email Field -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            อีเมล
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <input type="email" id="email" name="email" required 
                                   class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl 
                                          text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 
                                          focus:ring-indigo-500 focus:border-transparent transition-all
                                          bg-white/50 backdrop-blur-sm"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   placeholder="กรอกอีเมล">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full flex items-center justify-center px-4 py-3 text-white 
                                   bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl
                                   hover:from-indigo-700 hover:to-purple-700 
                                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500
                                   transform transition-all hover:-translate-y-0.5 hover:shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                        ส่งลิงก์ยืนยัน
                    </button>
                </form>

                <!-- Direct Reset Password Button -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">หรือ</span>
                    </div>
                </div>

                <a href="reset_password.php" 
                   class="w-full flex items-center justify-center px-4 py-3 text-white 
                          bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl
                          hover:from-purple-700 hover:to-pink-700 
                          focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500
                          transform transition-all hover:-translate-y-0.5 hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                        </path>
                    </svg>
                    รีเซ็ตรหัสผ่านโดยตรง
                </a>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        จำรหัสผ่านได้แล้ว? 
                        <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                            เข้าสู่ระบบ
                        </a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500">
                    © <?php echo date('Y'); ?> ระบบติดตามงาน. สงวนลิขสิทธิ์.
                </p>
            </div>
        </div>
    </div>
</body>
</html> 