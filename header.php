<?php       
// ตรวจสอบว่า session เริ่มต้นแล้วหรือยัง
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบติดตามงาน</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-gray-50 font-[Inter]">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo และ Navigation Links -->
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/task_tracking_system/admin/index.php" class="text-xl font-bold text-indigo-600">
                            <i class="bi bi-kanban"></i> Task Tracking
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="/task_tracking_system/admin/dashboard.php" 
                                   class="<?php echo strpos($current_page, 'dashboard.php') !== false ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    <i class="bi bi-speedometer2 mr-2"></i> แดชบอร์ด
                                </a>
                                <a href="/task_tracking_system/admin/users.php"
                                   class="<?php echo strpos($current_page, 'users.php') !== false ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    <i class="bi bi-people mr-2"></i> จัดการผู้ใช้
                                </a>
                                <a href="/task_tracking_system/admin/departments.php"
                                   class="<?php echo strpos($current_page, 'departments.php') !== false ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    <i class="bi bi-building mr-2"></i> จัดการแผนก
                                </a>
                            <?php else: ?>
                                <a href="/task_tracking_system/dashboard.php"
                                   class="<?php echo strpos($current_page, 'dashboard.php') !== false ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    <i class="bi bi-speedometer2 mr-2"></i> แดชบอร์ด
                                </a>
                            <?php endif; ?>
                            <a href="tasks.php"
                               class="<?php echo strpos($current_page, 'tasks.php') !== false ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                <i class="bi bi-list-task mr-2"></i> งาน
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right side buttons -->
                <div class="flex items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Profile dropdown -->
                        <div class="relative group">
                            <a href="/task_tracking_system/edit_profile.php" class="flex items-center space-x-3 cursor-pointer">
                                <img class="h-8 w-8 rounded-full object-cover border-2 border-gray-200 hover:border-indigo-500 transition-colors" 
                                     src="/task_tracking_system/uploads/profiles/<?php echo isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'default-profile.png'; ?>?v=<?php echo $_SESSION['profile_updated'] ?? time(); ?>" 
                                     alt="Profile">
                                <div class="ml-3 hover:text-indigo-600 transition-colors">
                                    <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-600"><?php echo $_SESSION['username']; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo ucfirst($_SESSION['role']); ?></p>
                                </div>
                            </a>
                        </div>
                        <!-- Logout button -->
                        <a href="/task_tracking_system/logout.php" 
                           class="ml-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="bi bi-box-arrow-right mr-2"></i> ออกจากระบบ
                        </a>
                    <?php else: ?>
                        <a href="/task_tracking_system/login.php" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="bi bi-box-arrow-in-right mr-2"></i> เข้าสู่ระบบ
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="bi bi-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
</body>
</html>