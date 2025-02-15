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

// ดึงกิจกรรมล่าสุด
$recent_activities = $conn->query("
    SELECT l.*, u.username 
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.user_id
    ORDER BY l.created_at DESC
    LIMIT 5
");

// ดึงผู้ใช้ที่เพิ่มล่าสุด
$recent_users = $conn->query("
    SELECT * FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4">แผงควบคุมผู้ดูแลระบบ</h2>

    <!-- สถิติภาพรวม -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">ผู้ใช้ทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">งานทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_tasks']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-list-check fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">งานที่รอดำเนินการ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending_tasks']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">งานที่เสร็จสิ้น</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['completed_tasks']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- เมนูลัด -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">เมนูด่วน</h5>
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <a href="users.php" class="btn btn-lg btn-outline-primary w-100">
                                <i class="bi bi-people"></i><br>จัดการผู้ใช้
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="departments.php" class="btn btn-lg btn-outline-success w-100">
                                <i class="bi bi-diagram-3"></i><br>จัดการแผนก
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="reports.php" class="btn btn-lg btn-outline-warning w-100">
                                <i class="bi bi-graph-up"></i><br>รายงาน
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="logs.php" class="btn btn-lg btn-outline-info w-100">
                                <i class="bi bi-clock-history"></i><br>ประวัติการใช้งาน
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- กิจกรรมล่าสุด -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">กิจกรรมล่าสุด</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php while ($activity = $recent_activities->fetch_assoc()): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($activity['username']); ?></h6>
                                <small><?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?></small>
                            </div>
                            <p class="mb-1"><?php echo htmlspecialchars($activity['description']); ?></p>
                            <small class="text-muted">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($activity['ip_address']); ?>
                            </small>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="logs.php" class="btn btn-sm btn-primary">ดูทั้งหมด</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ผู้ใช้ล่าสุด -->
        <div class="col-md-6 mb-4">
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
                                <small><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                            </div>
                            <p class="mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
                            <small class="text-muted">
                                บทบาท: <?php echo htmlspecialchars($user['role']); ?>
                            </small>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="users.php" class="btn btn-sm btn-primary">ดูทั้งหมด</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>