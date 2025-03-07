<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkManager();

// ดึงข้อมูลสถิติของทีม
$manager_id = $_SESSION['user_id'];
$stats = [
    'total_tasks' => $mysqli->query("SELECT COUNT(*) as count FROM tasks WHERE created_by = $manager_id")->fetch_assoc()['count'],
    'pending_tasks' => $mysqli->query("SELECT COUNT(*) as count FROM tasks WHERE created_by = $manager_id AND status = 'pending'")->fetch_assoc()['count'],
    'in_progress' => $mysqli->query("SELECT COUNT(*) as count FROM tasks WHERE created_by = $manager_id AND status = 'in_progress'")->fetch_assoc()['count'],
    'completed_tasks' => $mysqli->query("SELECT COUNT(*) as count FROM tasks WHERE created_by = $manager_id AND status = 'completed'")->fetch_assoc()['count']
];

// ดึงงานที่ใกล้ถึงกำหนด
$upcoming_tasks_sql = "
    SELECT t.*, u.username as assigned_username
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.user_id
    WHERE t.created_by = ? 
    AND t.status != 'completed'
    AND t.due_date >= CURRENT_DATE
    ORDER BY t.due_date ASC
    LIMIT 5";

$stmt = $mysqli->prepare($upcoming_tasks_sql);
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$upcoming_tasks = $stmt->get_result();

// ดึงประสิทธิภาพของทีม
$team_performance_sql = "
    SELECT u.username,
           COUNT(t.task_id) as total_tasks,
           SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks
    FROM users u
    LEFT JOIN tasks t ON u.user_id = t.assigned_to
    WHERE t.created_by = ?
    GROUP BY u.user_id
    ORDER BY completed_tasks DESC";

$stmt = $mysqli->prepare($team_performance_sql);
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$team_performance = $stmt->get_result();
?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-8">แดชบอร์ดผู้จัดการ</h2>

    <!-- สถิติ -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg">
            <div class="p-6">
                <h5 class="text-white text-lg font-semibold mb-2">งานทั้งหมด</h5>
                <p class="text-white text-4xl font-bold"><?php echo $stats['total_tasks']; ?></p>
            </div>
        </div>
        <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl shadow-lg">
            <div class="p-6">
                <h5 class="text-gray-800 text-lg font-semibold mb-2">รอดำเนินการ</h5>
                <p class="text-gray-800 text-4xl font-bold"><?php echo $stats['pending_tasks']; ?></p>
            </div>
        </div>
        <div class="bg-gradient-to-r from-cyan-400 to-cyan-500 rounded-xl shadow-lg">
            <div class="p-6">
                <h5 class="text-white text-lg font-semibold mb-2">กำลังดำเนินการ</h5>
                <p class="text-white text-4xl font-bold"><?php echo $stats['in_progress']; ?></p>
            </div>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg">
            <div class="p-6">
                <h5 class="text-white text-lg font-semibold mb-2">เสร็จสิ้น</h5>
                <p class="text-white text-4xl font-bold"><?php echo $stats['completed_tasks']; ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- งานที่ใกล้ถึงกำหนด -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-xl shadow-lg">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h5 class="text-xl font-semibold text-gray-800">งานที่ใกล้ถึงกำหนด</h5>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php while ($task = $upcoming_tasks->fetch_assoc()): ?>
                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition duration-200">
                                <div class="flex justify-between items-center">
                                    <h6 class="font-semibold text-gray-800"><?php echo htmlspecialchars($task['title']); ?></h6>
                                    <span class="text-red-500 text-sm font-medium">
                                        เหลือ <?php 
                                            $days = floor((strtotime($task['due_date']) - time()) / (60 * 60 * 24));
                                            echo $days . ' วัน'; 
                                        ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-2">
                                    ผู้รับผิดชอบ: <?php echo htmlspecialchars($task['assigned_username'] ?? 'ยังไม่ได้มอบหมาย'); ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ประสิทธิภาพทีม -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-xl shadow-lg">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h5 class="text-xl font-semibold text-gray-800">ประสิทธิภาพทีม</h5>
                </div>
                <div class="p-6">
                    <?php while ($member = $team_performance->fetch_assoc()): ?>
                        <div class="mb-6 last:mb-0">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($member['username']); ?></span>
                                <span class="text-sm text-gray-500">
                                    <?php 
                                        $completion_rate = $member['total_tasks'] > 0 
                                            ? round(($member['completed_tasks'] / $member['total_tasks']) * 100) 
                                            : 0;
                                        echo $completion_rate . '%';
                                    ?>
                                </span>
                            </div>
                            <div class="bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                     style="width: <?php echo $completion_rate; ?>%"></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 