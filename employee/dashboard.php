<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkEmployee();

$employee_id = $_SESSION['user_id'];

// ดึงข้อมูลสถิติของพนักงาน
$stats = [
    'total_tasks' => $conn->query("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = $employee_id")->fetch_assoc()['count'],
    'pending_tasks' => $conn->query("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = $employee_id AND status = 'pending'")->fetch_assoc()['count'],
    'in_progress' => $conn->query("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = $employee_id AND status = 'in_progress'")->fetch_assoc()['count'],
    'completed_tasks' => $conn->query("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = $employee_id AND status = 'completed'")->fetch_assoc()['count']
];

// ดึงงานที่ใกล้ถึงกำหนด
$upcoming_tasks_sql = "
    SELECT t.*, u.username as manager_name
    FROM tasks t
    LEFT JOIN users u ON t.created_by = u.user_id
    WHERE t.assigned_to = ? 
    AND t.status != 'completed'
    AND t.due_date >= CURRENT_DATE
    ORDER BY t.due_date ASC
    LIMIT 5";

$stmt = $conn->prepare($upcoming_tasks_sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$upcoming_tasks = $stmt->get_result();

// ดึงประสิทธิภาพการทำงาน
$performance_sql = "
    SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN due_date < CURRENT_DATE AND status != 'completed' THEN 1 ELSE 0 END) as overdue_tasks
    FROM tasks 
    WHERE assigned_to = ?";

$stmt = $conn->prepare($performance_sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$performance = $stmt->get_result()->fetch_assoc();
?>

<div class="container mt-4">
    <h2 class="mb-4">แดชบอร์ดพนักงาน</h2>

    <!-- สถิติ -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">งานทั้งหมด</h5>
                    <p class="card-text display-6"><?php echo $stats['total_tasks']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">รอดำเนินการ</h5>
                    <p class="card-text display-6"><?php echo $stats['pending_tasks']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">กำลังดำเนินการ</h5>
                    <p class="card-text display-6"><?php echo $stats['in_progress']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">เสร็จสิ้น</h5>
                    <p class="card-text display-6"><?php echo $stats['completed_tasks']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- งานที่ใกล้ถึงกำหนด -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">งานที่ใกล้ถึงกำหนด</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php while ($task = $upcoming_tasks->fetch_assoc()): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($task['title']); ?></h6>
                                    <small class="text-danger">
                                        เหลือ <?php 
                                            $days = floor((strtotime($task['due_date']) - time()) / (60 * 60 * 24));
                                            echo $days . ' วัน'; 
                                        ?>
                                    </small>
                                </div>
                                <p class="mb-1 small text-muted">
                                    ผู้มอบหมาย: <?php echo htmlspecialchars($task['manager_name']); ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ประสิทธิภาพการทำงาน -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">ประสิทธิภาพการทำงาน</h5>
                </div>
                <div class="card-body">
                    <?php
                    $completion_rate = $performance['total_tasks'] > 0 
                        ? round(($performance['completed_tasks'] / $performance['total_tasks']) * 100) 
                        : 0;
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>อัตราการเสร็จสิ้น</span>
                            <span class="text-muted"><?php echo $completion_rate; ?>%</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?php echo $completion_rate; ?>%"></div>
                        </div>
                    </div>
                    <div class="small text-muted">
                        <div>งานทั้งหมด: <?php echo $performance['total_tasks']; ?> งาน</div>
                        <div>เสร็จสิ้น: <?php echo $performance['completed_tasks']; ?> งาน</div>
                        <div>เลยกำหนด: <?php echo $performance['overdue_tasks']; ?> งาน</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 