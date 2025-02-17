<?php
require_once 'auth.php';
require_once 'db.php';
checkLogin();

$current_page = basename($_SERVER['PHP_SELF']);
include 'header.php';

// ดึงข้อมูลทีมงาน (จากแผนกเดียวกัน)
$user_id = $_SESSION['user_id'];
$sql = "SELECT u.*, d.name as department_name 
        FROM users u 
        LEFT JOIN departments d ON u.department_id = d.department_id 
        WHERE u.department_id = (SELECT department_id FROM users WHERE user_id = ?)
        AND u.user_id != ?
        ORDER BY u.username";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$team_members = $stmt->get_result();

// ดึงข้อมูลงานที่ทำร่วมกัน
$sql = "SELECT t.*, p.project_name, u.username as assigned_by_name 
        FROM tasks t 
        LEFT JOIN projects p ON t.project_id = p.project_id 
        LEFT JOIN users u ON t.created_by = u.user_id 
        WHERE t.project_id IN (
            SELECT DISTINCT project_id 
            FROM tasks 
            WHERE assigned_to = ?
        )
        AND t.assigned_to != ?
        ORDER BY t.due_date DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$shared_tasks = $stmt->get_result();
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">ทีมของฉัน</h1>
            <p class="mt-2 text-sm text-gray-700">ทำงานร่วมกันกับทีมของคุณ</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Team Members Section -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-medium text-gray-900">สมาชิกในทีม</h2>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php while ($member = $team_members->fetch_assoc()): ?>
                                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                                    <img class="h-12 w-12 rounded-full object-cover" 
                                         src="/task_tracking_system/uploads/profiles/<?php echo !empty($member['profile_image']) ? $member['profile_image'] : 'default-profile.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($member['username']); ?>">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($member['username']); ?></h3>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($member['department_name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($member['email']); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shared Tasks Section -->
            <div>
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-medium text-gray-900">งานที่ทำร่วมกัน</h2>
                    </div>
                    <div class="p-4">
                        <div class="space-y-4">
                            <?php while ($task = $shared_tasks->fetch_assoc()): ?>
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h3>
                                            <p class="text-xs text-gray-500 mt-1">โครงการ: <?php echo htmlspecialchars($task['project_name']); ?></p>
                                        </div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo match($task['status']) {
                                                'completed' => 'bg-green-100 text-green-800',
                                                'in_progress' => 'bg-yellow-100 text-yellow-800',
                                                'pending' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            }; ?>">
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
                                    <p class="text-sm text-gray-500 mt-2"><?php echo htmlspecialchars($task['description']); ?></p>
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
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 