<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkManager();

$manager_id = $_SESSION['user_id'];

// ดึงรายการพนักงานในทีม
$employees_sql = "SELECT user_id, username, email FROM users WHERE role = 'employee'";
$employees = $conn->query($employees_sql);

// ดึงรายการงานทั้งหมดของทีม
$tasks_sql = "
    SELECT t.*, u.username as assigned_username
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.user_id
    WHERE t.created_by = ?
    ORDER BY t.created_at DESC";

$stmt = $conn->prepare($tasks_sql);
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$tasks = $stmt->get_result();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>จัดการงาน</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="bi bi-plus-lg"></i> เพิ่มงานใหม่
        </button>
    </div>

    <!-- ตารางแสดงงาน -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>หัวข้อ</th>
                            <th>ผู้รับผิดชอบ</th>
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
                                    <td>
                                        <?php echo htmlspecialchars($task['assigned_username'] ?? 'ยังไม่ได้มอบหมาย'); ?>
                                    </td>
                                    <td>
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
                                    <td><?php echo date('d/m/Y', strtotime($task['due_date'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/tts-project/manager/view_task.php?id=<?php echo $task['task_id']; ?>" 
                                               class="btn btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="/tts-project/manager/edit_task.php?id=<?php echo $task['task_id']; ?>" 
                                               class="btn btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="/tts-project/manager/delete_task.php?id=<?php echo $task['task_id']; ?>" 
                                               class="btn btn-danger"
                                               onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบงานนี้?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
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

<!-- Modal เพิ่มงานใหม่ -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มงานใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addTaskForm" action="/tts-project/manager/add_task.php" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">หัวข้องาน</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">รายละเอียด</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">มอบหมายให้</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">เลือกผู้รับผิดชอบ</option>
                            <?php 
                            $employees->data_seek(0); // รีเซ็ตตำแหน่งข้อมูล
                            while ($employee = $employees->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $employee['user_id']; ?>">
                                    <?php echo htmlspecialchars($employee['username'] . ' (' . $employee['email'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">ความสำคัญ</label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="low">ต่ำ</option>
                                <option value="medium">ปานกลาง</option>
                                <option value="high">สูง</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">กำหนดส่ง</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" form="addTaskForm" class="btn btn-primary">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewTask(taskId) {
    // TODO: Implement view task details
    window.location.href = `view_task.php?id=${taskId}`;
}

function editTask(taskId) {
    // TODO: Implement edit task
    window.location.href = `edit_task.php?id=${taskId}`;
}

function deleteTask(taskId) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบงานนี้?')) {
        // TODO: Implement delete task
        window.location.href = `delete_task.php?id=${taskId}`;
    }
}
</script>

<?php include '../footer.php'; ?> 