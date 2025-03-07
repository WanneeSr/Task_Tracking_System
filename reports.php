<?php
require_once 'auth.php';
require_once 'db.php';
checkLogin();

$current_page = basename($_SERVER['PHP_SELF']);
include 'header.php';

// ดึงข้อมูลสถิติงานทั้งหมด
$user_id = $_SESSION['user_id'];
$sql = "SELECT 
            COUNT(*) as total_tasks,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_tasks
        FROM tasks 
        WHERE assigned_to = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// ดึงข้อมูลงานล่าสุดที่อัพเดท
$sql = "SELECT t.*, p.project_name, u.username as assigned_by_name 
        FROM tasks t 
        LEFT JOIN projects p ON t.project_id = p.project_id 
        LEFT JOIN users u ON t.created_by = u.user_id 
        WHERE t.assigned_to = ? 
        ORDER BY t.updated_at DESC 
        LIMIT 5";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_updates = $stmt->get_result();

// ดึงข้อมูลงานตามความสำคัญ
$sql = "SELECT priority, COUNT(*) as count 
        FROM tasks 
        WHERE assigned_to = ? 
        GROUP BY priority";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$priority_stats = $stmt->get_result();
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">รายงานการทำงาน</h1>
            <p class="mt-2 text-sm text-gray-700">ดูสถิติและความคืบหน้าของงานทั้งหมด</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Tasks -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-500">
                        <i class="bi bi-list-task text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">งานทั้งหมด</h3>
                        <p class="text-3xl font-bold text-indigo-600"><?php echo $stats['total_tasks']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Pending Tasks -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                        <i class="bi bi-clock text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">รอดำเนินการ</h3>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['pending_tasks']; ?></p>
                    </div>
                </div>
            </div>

            <!-- In Progress Tasks -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                        <i class="bi bi-arrow-repeat text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">กำลังดำเนินการ</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $stats['in_progress_tasks']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Completed Tasks -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500">
                        <i class="bi bi-check-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">เสร็จสิ้น</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo $stats['completed_tasks']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Updates -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">งานที่อัพเดทล่าสุด</h2>
                    <div class="space-y-4">
                        <?php while ($task = $recent_updates->fetch_assoc()): ?>
                            <div class="border-b pb-4 last:border-b-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h3>
                                        <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($task['description']); ?></p>
                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                            <i class="bi bi-clock mr-1"></i>
                                            <span>อัพเดทเมื่อ: <?php echo date('d/m/Y H:i', strtotime($task['updated_at'])); ?></span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?php echo match($task['status']) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        }; ?>">
                                        <?php echo match($task['status']) {
                                            'pending' => 'รอดำเนินการ',
                                            'in_progress' => 'กำลังดำเนินการ',
                                            'completed' => 'เสร็จสิ้น',
                                            'cancelled' => 'ยกเลิก',
                                            default => $task['status']
                                        }; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Priority Distribution -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">การกระจายความสำคัญของงาน</h2>
                    <div class="space-y-4">
                        <?php while ($priority = $priority_stats->fetch_assoc()): ?>
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">
                                        <?php echo match($priority['priority']) {
                                            'low' => 'ต่ำ',
                                            'medium' => 'ปานกลาง',
                                            'high' => 'สูง',
                                            'urgent' => 'เร่งด่วน',
                                            default => $priority['priority']
                                        }; ?>
                                    </span>
                                    <span class="text-sm text-gray-500"><?php echo $priority['count']; ?> งาน</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full 
                                        <?php echo match($priority['priority']) {
                                            'low' => 'bg-green-500',
                                            'medium' => 'bg-blue-500',
                                            'high' => 'bg-yellow-500',
                                            'urgent' => 'bg-red-500',
                                            default => 'bg-gray-500'
                                        }; ?>" 
                                        style="width: <?php echo ($stats['total_tasks'] > 0) ? ($priority['count'] / $stats['total_tasks'] * 100) : 0; ?>%">
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 