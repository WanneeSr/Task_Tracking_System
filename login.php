<?php
session_start();
require_once 'db.php';

// ถ้ามีการล็อกอินอยู่แล้ว ให้ไปที่หน้า tasks.php
if (isset($_SESSION['user_id'])) {
    header('Location: /task_tracking_system/index.php');
    exit();
}

// ถ้ามีการ submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบว่ามีการส่งข้อมูลมาครบหรือไม่
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        try {
            // เตรียมคำสั่ง SQL ด้วย prepared statement
            $sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }

            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // ตรวจสอบรหัสผ่าน
                if (password_verify($password, $user['password'])) {
                    // สร้าง session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect ตาม role
                    switch ($user['role']) {
                        case 'admin':
                            header('Location: /task_tracking_system/admin/index.php');
                            break;
                        case 'manager':
                            header('Location: manager/dashboard.php');
                            break;
                        case 'employee':
                            header('Location: /task_tracking_system/index.php');
                            break;
                        default:
                            $error = "ไม่พบสิทธิ์การเข้าใช้งานที่เหมาะสม";
                            session_destroy();
                            break;
                    }
                    
                    if (!isset($error)) {
                        exit();
                    }
                } else {
                    $error = "รหัสผ่านไม่ถูกต้อง";
                }
            } else {
                $error = "ไม่พบบัญชีผู้ใช้นี้";
            }
            
            $stmt->close();

        } catch (Exception $e) {
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    } else {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}

// ถ้ามีการล็อกอินแล้ว ให้ redirect ไปหน้าที่เหมาะสม
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: /task_tracking_system/admin/index.php');
            break;
        case 'manager':
            header('Location: manager/dashboard.php');
            break;
        case 'employee':
            header('Location: /task_tracking_system/index.php');
            break;
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบติดตามงาน</title>
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
            <!-- Login Card -->
            <div class="glass-effect rounded-3xl shadow-2xl p-8 space-y-8 border border-white/20">
                <!-- Logo & Title -->
                <div class="text-center space-y-2">
                    <div class="flex justify-center">
                        <div class="p-3 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">
                        เข้าสู่ระบบ
                    </h2>
                    <p class="text-gray-500">ระบบติดตามงาน</p>
                </div>

                <?php if (isset($error)): ?>
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
                                <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Login Form -->
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

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full flex items-center justify-center px-4 py-3 text-white 
                                   bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl
                                   hover:from-indigo-700 hover:to-purple-700 
                                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500
                                   transform transition-all hover:-translate-y-0.5 hover:shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                            </path>
                        </svg>
                        เข้าสู่ระบบ
                    </button>
                </form>
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