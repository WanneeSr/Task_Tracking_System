<?php
require_once 'auth.php';
require_once 'db.php';
checkLogin();

$current_page = basename($_SERVER['PHP_SELF']);
include 'header.php';

// ดึงข้อมูลงานทั้งหมดที่เกี่ยวข้องกับผู้ใช้
$user_id = $_SESSION['user_id'];
$sql = "SELECT t.*, p.project_name, u.username as assigned_by_name 
        FROM tasks t 
        LEFT JOIN projects p ON t.project_id = p.project_id 
        LEFT JOIN users u ON t.created_by = u.user_id 
        WHERE t.assigned_to = ? 
        ORDER BY t.due_date ASC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// จัดกลุ่มงานตามสถานะ
$tasks = [
    'pending' => [],
    'in_progress' => [],
    'completed' => []
];

while ($task = $result->fetch_assoc()) {
    $tasks[$task['status']][] = $task;
}
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Kanban Board</h1>
            <p class="mt-2 text-sm text-gray-700">จัดการงานด้วยระบบ Kanban</p>
        </div>

        <!-- Kanban Board -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Pending Column -->
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="bi bi-clock text-yellow-500 mr-2"></i>
                    รอดำเนินการ
                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        <?php echo count($tasks['pending']); ?>
                    </span>
                </h2>
                <div class="space-y-4">
                    <?php foreach ($tasks['pending'] as $task): ?>
                        <div class="bg-gray-50 p-4 rounded-lg hover:shadow transition-shadow">
                            <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($task['description']); ?></p>
                            <div class="mt-2 flex justify-between items-center">
                                <span class="text-xs text-gray-500">
                                    กำหนดส่ง: <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>
                                </span>
                                <a href="view_task.php?id=<?php echo $task['task_id']; ?>" 
                                   class="text-indigo-600 hover:text-indigo-900 text-sm">
                                    ดูรายละเอียด
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- In Progress Column -->
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="bi bi-arrow-repeat text-blue-500 mr-2"></i>
                    กำลังดำเนินการ
                    <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        <?php echo count($tasks['in_progress']); ?>
                    </span>
                </h2>
                <div class="space-y-4">
                    <?php foreach ($tasks['in_progress'] as $task): ?>
                        <div class="bg-gray-50 p-4 rounded-lg hover:shadow transition-shadow">
                            <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($task['description']); ?></p>
                            <div class="mt-2 flex justify-between items-center">
                                <span class="text-xs text-gray-500">
                                    กำหนดส่ง: <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>
                                </span>
                                <a href="view_task.php?id=<?php echo $task['task_id']; ?>" 
                                   class="text-indigo-600 hover:text-indigo-900 text-sm">
                                    ดูรายละเอียด
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Completed Column -->
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="bi bi-check-circle text-green-500 mr-2"></i>
                    เสร็จสิ้น
                    <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        <?php echo count($tasks['completed']); ?>
                    </span>
                </h2>
                <div class="space-y-4">
                    <?php foreach ($tasks['completed'] as $task): ?>
                        <div class="bg-gray-50 p-4 rounded-lg hover:shadow transition-shadow">
                            <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($task['description']); ?></p>
                            <div class="mt-2 flex justify-between items-center">
                                <span class="text-xs text-gray-500">
                                    เสร็จเมื่อ: <?php echo date('d/m/Y', strtotime($task['updated_at'])); ?>
                                </span>
                                <a href="view_task.php?id=<?php echo $task['task_id']; ?>" 
                                   class="text-indigo-600 hover:text-indigo-900 text-sm">
                                    ดูรายละเอียด
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 