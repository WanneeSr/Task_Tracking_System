<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkManager();

if (!isset($_GET['id'])) {
    header('Location: tasks.php');
    exit();
}

$task_id = $_GET['id'];
$manager_id = $_SESSION['user_id'];

// ดึงข้อมูลงาน
$sql = "SELECT t.*, 
               u1.username as assigned_username,
               u2.username as created_username
        FROM tasks t 
        LEFT JOIN users u1 ON t.assigned_to = u1.user_id
        LEFT JOIN users u2 ON t.created_by = u2.user_id
        WHERE t.task_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();

if (!$task) {
    $_SESSION['error'] = "ไม่พบงานที่ระบุ";
    header('Location: tasks.php');
    exit();
}

// แสดงรายละเอียดงาน
?>
<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">รายละเอียดงาน</h5>
            <div>
                <a href="edit_task.php?id=<?php echo $task_id; ?>" class="btn btn-primary btn-sm">แก้ไข</a>
                <a href="tasks.php" class="btn btn-secondary btn-sm">กลับ</a>
            </div>
        </div>
        <div class="card-body">
            <h4><?php echo htmlspecialchars($task['title']); ?></h4>
            <p class="text-muted">
                สร้างโดย: <?php echo htmlspecialchars($task['created_username']); ?> | 
                สร้างเมื่อ: <?php echo date('d/m/Y H:i', strtotime($task['created_at'])); ?>
            </p>
            <hr>
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>ผู้รับผิดชอบ:</strong> <?php echo htmlspecialchars($task['assigned_username'] ?? 'ยังไม่ได้มอบหมาย'); ?></p>
                    <p><strong>สถานะ:</strong> 
                        <span class="badge bg-<?php 
                            echo match($task['status']) {
                                'pending' => 'warning',
                                'in_progress' => 'primary',
                                'completed' => 'success',
                                default => 'secondary'
                            };
                        ?>">
                            <?php 
                            echo match($task['status']) {
                                'pending' => 'รอดำเนินการ',
                                'in_progress' => 'กำลังดำเนินการ',
                                'completed' => 'เสร็จสิ้น',
                                default => 'ไม่ระบุ'
                            };
                            ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>ความสำคัญ:</strong> 
                        <span class="badge bg-<?php 
                            echo match($task['priority']) {
                                'low' => 'success',
                                'medium' => 'warning',
                                'high' => 'danger',
                                default => 'secondary'
                            };
                        ?>">
                            <?php 
                            echo match($task['priority']) {
                                'low' => 'ต่ำ',
                                'medium' => 'ปานกลาง', 
                                'high' => 'สูง',
                                default => 'ไม่ระบุ'
                            };
                            ?>
                        </span>
                    </p>
                    <p><strong>กำหนดส่ง:</strong> <?php echo date('d/m/Y', strtotime($task['due_date'])); ?></p>
                </div>
            </div>
            <div class="mb-3">
                <h6>รายละเอียด:</h6>
                <p class="border p-3 bg-light">
                    <?php echo nl2br(htmlspecialchars($task['description'] ?? 'ไม่มีรายละเอียด')); ?>
                </p>
            </div>
        </div>
    </div>
</div>
<?php include '../footer.php'; ?>