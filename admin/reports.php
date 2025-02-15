<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// ดึงข้อมูลสถิติรายเดือน
$monthly_stats_sql = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks
    FROM tasks 
    WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC";

$monthly_stats = $conn->query($monthly_stats_sql);
if (!$monthly_stats) {
    die("Error: " . $conn->error);
}

// ดึงข้อมูลประสิทธิภาพของผู้ใช้
$user_performance_sql = "
    SELECT 
        u.username,
        COUNT(t.task_id) as total_assigned,
        SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
        SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) as pending_tasks
    FROM users u
    LEFT JOIN tasks t ON u.user_id = t.assigned_to
    WHERE u.role != 'admin'
    GROUP BY u.user_id
    ORDER BY completed_tasks DESC";

$user_performance = $conn->query($user_performance_sql);
if (!$user_performance) {
    die("Error: " . $conn->error);
}

// ดึงข้อมูลงานที่เลยกำหนด
$overdue_tasks_sql = "
    SELECT t.*, u.username
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.user_id
    WHERE t.status != 'completed' 
    AND t.due_date < CURRENT_DATE
    ORDER BY t.due_date ASC";

$overdue_tasks = $conn->query($overdue_tasks_sql);
if (!$overdue_tasks) {
    die("Error: " . $conn->error);
}
?>

<div class="container mt-4">
    <h2 class="mb-4">รายงาน</h2>

    <!-- สถิติรายเดือน -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">สถิติรายเดือน</h5>
            <a href="export_report.php?type=monthly" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>เดือน</th>
                            <th>งานทั้งหมด</th>
                            <th>เสร็จสิ้น</th>
                            <th>กำลังดำเนินการ</th>
                            <th>รอดำเนินการ</th>
                            <th>อัตราความสำเร็จ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($month = $monthly_stats->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></td>
                                <td><?php echo $month['total_tasks']; ?></td>
                                <td><?php echo $month['completed_tasks']; ?></td>
                                <td><?php echo $month['in_progress_tasks']; ?></td>
                                <td><?php echo $month['pending_tasks']; ?></td>
                                <td>
                                    <?php 
                                    $success_rate = $month['total_tasks'] > 0 
                                        ? round(($month['completed_tasks'] / $month['total_tasks']) * 100, 1) 
                                        : 0;
                                    echo $success_rate . '%';
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ประสิทธิภาพของผู้ใช้ -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">ประสิทธิภาพของผู้ใช้</h5>
            <a href="export_report.php?type=performance" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ผู้ใช้</th>
                            <th>งานทั้งหมด</th>
                            <th>เสร็จสิ้น</th>
                            <th>กำลังดำเนินการ</th>
                            <th>รอดำเนินการ</th>
                            <th>อัตราความสำเร็จ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $user_performance->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo $user['total_assigned']; ?></td>
                                <td><?php echo $user['completed_tasks']; ?></td>
                                <td><?php echo $user['in_progress_tasks']; ?></td>
                                <td><?php echo $user['pending_tasks']; ?></td>
                                <td>
                                    <?php 
                                    $success_rate = $user['total_assigned'] > 0 
                                        ? round(($user['completed_tasks'] / $user['total_assigned']) * 100, 1) 
                                        : 0;
                                    echo $success_rate . '%';
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- งานที่เลยกำหนด -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">งานที่เลยกำหนด</h5>
            <a href="export_report.php?type=overdue" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>งาน</th>
                            <th>ผู้รับผิดชอบ</th>
                            <th>กำหนดส่ง</th>
                            <th>เลยกำหนด</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($task = $overdue_tasks->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td><?php echo htmlspecialchars($task['username'] ?? 'ไม่ระบุ'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($task['due_date'])); ?></td>
                                <td>
                                    <?php 
                                    $days_overdue = floor((strtotime('now') - strtotime($task['due_date'])) / (60 * 60 * 24));
                                    echo $days_overdue . ' วัน';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge text-bg-<?php 
                                        echo match($task['status']) {
                                            'in_progress' => 'warning',
                                            'pending' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php 
                                        echo match($task['status']) {
                                            'in_progress' => 'กำลังดำเนินการ',
                                            'pending' => 'รอดำเนินการ',
                                            default => 'ไม่ระบุ'
                                        };
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 