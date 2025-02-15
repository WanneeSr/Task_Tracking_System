<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkEmployee();

$employee_id = $_SESSION['user_id'];

// ดึงรายการงานทั้งหมดของพนักงาน
$tasks_sql = "
    SELECT t.*, u.username as manager_name
    FROM tasks t
    LEFT JOIN users u ON t.created_by = u.user_id
    WHERE t.assigned_to = ?
    ORDER BY t.due_date ASC";

$stmt = $conn->prepare($tasks_sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$tasks = $stmt->get_result();
?>

<div class="container mt-4">
    <h2 class="mb-4">รายการงานของฉัน</h2>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>หัวข้อ</th>
                            <th>ผู้มอบหมาย</th>
                            <th>สถานะ</th>
                            <th>ความสำคัญ</th>
                            <th>กำหนดส่ง</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($tasks->num_rows > 0): ?>
                            <?php while ($task = $tasks->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['manager_name']); ?></td>
                                    <td>
                                        <select class="form-select form-select-sm status-select" 
                                                data-task-id="<?php echo $task['task_id']; ?>"
                                                <?php echo $task['status'] === 'completed' ? 'disabled' : ''; ?>>
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
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <?php 
                                        $due_date = strtotime($task['due_date']);
                                        $today = strtotime('today');
                                        $date_class = $due_date < $today ? 'text-danger' : '';
                                        echo "<span class='$date_class'>" . date('d/m/Y', $due_date) . "</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <a href="/tts-project/employee/view_task.php?id=<?php echo $task['task_id']; ?>" 
                                           class="btn btn-info btn-sm">
                                            <i class="bi bi-eye"></i> ดูรายละเอียด
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">ไม่พบรายการงาน</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
                // รีเซ็ตกลับไปเป็นค่าเดิม
                this.value = this.getAttribute('data-original-status');
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