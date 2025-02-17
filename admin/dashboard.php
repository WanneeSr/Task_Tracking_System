<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// ดึงข้อมูลสถิติ
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'total_tasks' => $conn->query("SELECT COUNT(*) as count FROM tasks")->fetch_assoc()['count'],
    'pending_tasks' => $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'pending'")->fetch_assoc()['count'],
    'completed_tasks' => $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'completed'")->fetch_assoc()['count']
];

// ดึงงานล่าสุด
$recent_tasks_sql = "SELECT t.*, u.username as assigned_username
                    FROM tasks t
                    LEFT JOIN users u ON t.assigned_to = u.user_id
                    ORDER BY t.created_at DESC 
                    LIMIT 5";
$recent_tasks = $conn->query($recent_tasks_sql);

// ดึงผู้ใช้ล่าสุด
$recent_users_sql = "SELECT user_id, username, email, role, created_at 
                     FROM users 
                     ORDER BY created_at DESC 
                     LIMIT 5";
$recent_users = $conn->query($recent_users_sql);

// ตรวจสอบการ query
if (!$recent_tasks || !$recent_users) {
    die("Error: " . $conn->error);
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <!-- หัวข้อหลัก -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">แดชบอร์ดผู้ดูแลระบบ</h2>
            <p class="text-gray-600 mt-2">ภาพรวมของระบบและสถิติที่สำคัญ</p>
        </div>

        <!-- สถิติการ์ด -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- ผู้ใช้ทั้งหมด -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-8">
                    <div class="flex items-center justify-between">
                        <div class="text-white">
                            <p class="text-sm font-medium opacity-75">ผู้ใช้ทั้งหมด</p>
                            <p class="text-3xl font-bold mt-1"><?php echo $stats['total_users']; ?></p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-25 rounded-lg p-3">
                            <i class="bi bi-people-fill text-2xl text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- งานทั้งหมด -->
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-8">
                    <div class="flex items-center justify-between">
                        <div class="text-white">
                            <p class="text-sm font-medium opacity-75">งานทั้งหมด</p>
                            <p class="text-3xl font-bold mt-1"><?php echo $stats['total_tasks']; ?></p>
                        </div>
                        <div class="bg-indigo-400 bg-opacity-25 rounded-lg p-3">
                            <i class="bi bi-list-task text-2xl text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- งานที่รอดำเนินการ -->
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-8">
                    <div class="flex items-center justify-between">
                        <div class="text-white">
                            <p class="text-sm font-medium opacity-75">รอดำเนินการ</p>
                            <p class="text-3xl font-bold mt-1"><?php echo $stats['pending_tasks']; ?></p>
                        </div>
                        <div class="bg-amber-400 bg-opacity-25 rounded-lg p-3">
                            <i class="bi bi-hourglass-split text-2xl text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- งานที่เสร็จสิ้น -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-8">
                    <div class="flex items-center justify-between">
                        <div class="text-white">
                            <p class="text-sm font-medium opacity-75">เสร็จสิ้น</p>
                            <p class="text-3xl font-bold mt-1"><?php echo $stats['completed_tasks']; ?></p>
                        </div>
                        <div class="bg-green-400 bg-opacity-25 rounded-lg p-3">
                            <i class="bi bi-check2-circle text-2xl text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ส่วนแสดงข้อมูล -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- งานล่าสุด -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">งานล่าสุด</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php while ($task = $recent_tasks->fetch_assoc()): ?>
                                <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition duration-200">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-800">
                                                <?php echo htmlspecialchars($task['title']); ?>
                                            </h4>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <?php echo htmlspecialchars($task['description']); ?>
                                            </p>
                                            <div class="mt-2 flex items-center space-x-2">
                                                <span class="text-xs text-gray-500">
                                                    <i class="bi bi-person"></i>
                                                    <?php echo htmlspecialchars($task['assigned_username'] ?? 'ไม่ระบุ'); ?>
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php 
                                                    echo match($task['status']) {
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'in_progress' => 'bg-yellow-100 text-yellow-800',
                                                        'pending' => 'bg-gray-100 text-gray-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                ?>">
                                                    <?php 
                                                    echo match($task['status']) {
                                                        'completed' => 'เสร็จสิ้น',
                                                        'in_progress' => 'กำลังดำเนินการ',
                                                        'pending' => 'รอดำเนินการ',
                                                        default => 'ไม่ระบุ'
                                                    };
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500">
                                            <?php echo date('d/m/Y', strtotime($task['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ผู้ใช้ล่าสุด -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">ผู้ใช้ล่าสุด</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                                <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg transition duration-200">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <i class="bi bi-person text-gray-500"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </p>
                                        <p class="text-sm text-gray-500 truncate">
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php 
                                        echo match($user['role']) {
                                            'admin' => 'bg-red-100 text-red-800',
                                            'manager' => 'bg-blue-100 text-blue-800',
                                            'employee' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo $user['role']; ?>
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 