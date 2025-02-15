<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkEmployee();

if (!isset($_GET['id'])) {
    header('Location: tasks.php');
    exit();
}

$task_id = $_GET['id'];
$employee_id = $_SESSION['user_id'];

// ดึงข้อมูลงาน
$sql = "SELECT t.*, 
               u.username as manager_name,
               u.email as manager_email
        FROM tasks t 
        LEFT JOIN users u ON t.created_by = u.user_id
        WHERE t.task_id = ? AND t.assigned_to = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $task_id, $employee_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();

if (!$task) {
    $_SESSION['error'] = "ไม่พบงานที่ระบุ หรือคุณไม่มีสิทธิ์ดูงานนี้";
    header('Location: tasks.php');
    exit();
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">รายละเอียดงาน</h3>
            <a href="tasks.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> กลับ
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4><?php echo htmlspecialchars($task['title']); ?></h4>
                    <div class="mb-4">
                        <h5 class="text-muted">รายละเอียด</h5>
                        <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                    </div>
                    
                    <?php if ($task['status'] !== 'completed'): ?>
                        <div class="mb-4">
                            <h5>อัพเดทสถานะ</h5>
                            <select class="form-select status-select" data-task-id="<?php echo $task_id; ?>">
                                <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>
                                    รอดำเนินการ
                                </option>
                                <option value="in_progress" <?php echo $task['status'] === 'in_progress' ? 'selected' : ''; ?>>
                                    กำลังดำเนินการ
                                </option>
                                <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>
                                    เสร็จสิ้น
                                </option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <p class="mb-2">
                                <strong>สถานะ:</strong>
                                <span class="badge text-bg-<?php 
                                    echo match($task['status']) {
                                        'completed' => 'success',
                                        'in_progress' => 'warning',
                                        'pending' => 'danger',
                                        default => 'secondary'
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
                            </p>
                            <p class="mb-2">
                                <strong>ความสำคัญ:</strong>
                                <span class="badge text-bg-<?php 
                                    echo match($task['priority']) {
                                        'high' => 'danger',
                                        'medium' => 'warning',
                                        'low' => 'info',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php 
                                    echo match($task['priority']) {
                                        'high' => 'สูง',
                                        'medium' => 'ปานกลาง',
                                        'low' => 'ต่ำ',
                                        default => 'ไม่ระบุ'
                                    };
                                    ?>
                                </span>
                            </p>
                            <p class="mb-2">
                                <strong>ผู้มอบหมาย:</strong><br>
                                <?php echo htmlspecialchars($task['manager_name']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($task['manager_email']); ?></small>
                            </p>
                            <p class="mb-2">
                                <strong>กำหนดส่ง:</strong><br>
                                <?php 
                                $due_date = strtotime($task['due_date']);
                                $today = strtotime('today');
                                $date_class = $due_date < $today ? 'text-danger' : '';
                                echo "<span class='$date_class'>" . date('d/m/Y', $due_date) . "</span>";
                                
                                if ($due_date < $today && $task['status'] !== 'completed') {
                                    echo '<br><small class="text-danger">เลยกำหนดส่ง</small>';
                                }
                                ?>
                            </p>
                            <p class="mb-0">
                                <strong>สร้างเมื่อ:</strong><br>
                                <?php echo date('d/m/Y H:i', strtotime($task['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const taskId = this.dataset.taskId;
        const status = this.value;
        
        fetch('/tts-project/employee/update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `task_id=${taskId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (status === 'completed') {
                    this.disabled = true;
                }
                alert('อัพเดทสถานะเรียบร้อยแล้ว');
                // รีโหลดหน้าเพื่อแสดงการเปลี่ยนแปลง
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการอัพเดทสถานะ');
        });
    });
});
</script>

<?php include '../footer.php'; ?> 