<?php
session_start();
require_once 'db.php';

// ถ้ามีการล็อกอินอยู่แล้ว ให้ไปที่หน้า index.php
if (isset($_SESSION['user_id'])) {
    header('Location: /task_tracking_system/index.php');
    exit();
}

// ถ้ามีการ submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];

    // ตรวจสอบข้อมูล
    if (empty($username)) {
        $errors[] = "กรุณากรอกชื่อผู้ใช้";
    } elseif (strlen($username) < 3) {
        $errors[] = "ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร";
    }

    if (empty($email)) {
        $errors[] = "กรุณากรอกอีเมล";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "รูปแบบอีเมลไม่ถูกต้อง";
    }

    if (empty($password)) {
        $errors[] = "กรุณากรอกรหัสผ่าน";
    } elseif (strlen($password) < 6) {
        $errors[] = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    }

    if ($password !== $confirm_password) {
        $errors[] = "รหัสผ่านไม่ตรงกัน";
    }

    // ตรวจสอบว่ามี username หรือ email ซ้ำหรือไม่
    if (empty($errors)) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            $errors[] = "ชื่อผู้ใช้หรืออีเมลนี้มีผู้ใช้งานแล้ว";
        }
    }

    // ถ้าไม่มี error ให้บันทึกข้อมูล
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'employee'; // กำหนดค่าเริ่มต้นเป็น employee
        
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ";
            header('Location: login.php');
            exit();
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $mysqli->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - ระบบติดตามงาน</title>
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
            <!-- Register Card -->
            <div class="glass-effect rounded-3xl shadow-2xl p-8 space-y-8 border border-white/20">
                <!-- Logo & Title -->
                <div class="text-center space-y-2">
                    <div class="flex justify-center">
                        <div class="p-3 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">
                        สมัครสมาชิก
                    </h2>
                    <p class="text-gray-500">สร้างบัญชีใหม่สำหรับระบบติดตามงาน</p>
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

                <!-- Register Form -->
                <form method="POST" class="space-y-6">
                    <!-- Username Field -->
                    <div class="space-y-2">
                        <label for="username" class="block text-sm font-medium text-gray-700">
                            ชื่อผู้ใช้
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                    </path>
                                </svg>
                            </div>
                            <input type="text" id="username" name="username" required 
                                   class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl 
                                          text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 
                                          focus:ring-indigo-500 focus:border-transparent transition-all
                                          bg-white/50 backdrop-blur-sm"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   placeholder="กรอกชื่อผู้ใช้">
                        </div>
                    </div>

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

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            รหัสผ่าน
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required 
                                   class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl 
                                          text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 
                                          focus:ring-indigo-500 focus:border-transparent transition-all
                                          bg-white/50 backdrop-blur-sm"
                                   placeholder="กรอกรหัสผ่าน">
                        </div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="space-y-2">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                            ยืนยันรหัสผ่าน
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password" required 
                                   class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl 
                                          text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 
                                          focus:ring-indigo-500 focus:border-transparent transition-all
                                          bg-white/50 backdrop-blur-sm"
                                   placeholder="กรอกรหัสผ่านอีกครั้ง">
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
                                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                            </path>
                        </svg>
                        สมัครสมาชิก
                    </button>
                </form>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        มีบัญชีอยู่แล้ว? 
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