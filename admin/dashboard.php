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

<div class="container mt-4">
    <h2 class="mb-4">แดชบอร์ด</h2>

    <!-- สถิติ -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-bg-primary">
                <div class="card-body">
                    <h5 class="card-title">ผู้ใช้ทั้งหมด</h5>
                    <p class="card-text h2"><?php echo $stats['total_users']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-info">
                <div class="card-body">
                    <h5 class="card-title">งานทั้งหมด</h5>
                    <p class="card-text h2"><?php echo $stats['total_tasks']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-warning">
                <div class="card-body">
                    <h5 class="card-title">งานที่รอดำเนินการ</h5>
                    <p class="card-text h2"><?php echo $stats['pending_tasks']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-success">
                <div class="card-body">
                    <h5 class="card-title">งานที่เสร็จสิ้น</h5>
                    <p class="card-text h2"><?php echo $stats['completed_tasks']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- งานล่าสุด -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">งานล่าสุด</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php while ($task = $recent_tasks->fetch_assoc()): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($task['title']); ?></h6>
                                    <small>
                                        <?php echo date('d/m/Y', strtotime($task['created_at'])); ?>
                                    </small>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($task['description']); ?></p>
                                <small>
                                    ผู้รับผิดชอบ: <?php echo htmlspecialchars($task['assigned_username'] ?? 'ไม่ระบุ'); ?>
                                    <span class="badge text-bg-<?php 
                                        echo match($task['status']) {
                                            'completed' => 'success',
                                            'in_progress' => 'warning',
                                            'pending' => 'secondary',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php 
                                        echo match($task['status']) {
                                            'completed' => 'เสร็จสิ้น',
                                            'in_progress' => 'กำลังดำเนินการ',
                                            'pending' => 'รอดำเนินการ',
                                            default => 'รอดำเนินการ'
                                        };
                                        ?>
                                    </span>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ผู้ใช้ล่าสุด -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">ผู้ใช้ล่าสุด</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php while ($user = $recent_users->fetch_assoc()): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($user['username']); ?></h6>
                                    <small>
                                        <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                    </small>
                                </div>
                                <small>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                    <span class="badge text-bg-<?php 
                                        echo match($user['role']) {
                                            'admin' => 'danger',
                                            'manager' => 'warning',
                                            'employee' => 'secondary',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo $user['role']; ?>
                                    </span>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 