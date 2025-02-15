<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// ดึงข้อมูลงานทั้งหมด
$sql = "SELECT t.*, u.username as assigned_username, c.username as creator_username
        FROM tasks t 
        LEFT JOIN users u ON t.assigned_to = u.user_id 
        LEFT JOIN users c ON t.created_by = c.user_id
        ORDER BY t.created_at DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    die("Execute failed: " . $stmt->error);
}
?>

<div class="container mt-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
            <?php 
            foreach ($_SESSION['errors'] as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            unset($_SESSION['errors']);
            ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>จัดการงาน</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="bi bi-plus-circle"></i> เพิ่มงาน
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>หัวข้อ</th>
                            <th>รายละเอียด</th>
                            <th>ผู้รับผิดชอบ</th>
                            <th>ผู้สร้าง</th>
                            <th>สถานะ</th>
                            <th>ความสำคัญ</th>
                            <th>กำหนดส่ง</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($task = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $task['task_id']; ?></td>
                            <td><?php echo htmlspecialchars($task['title']); ?></td>
                            <td class="text-truncate" style="max-width: 200px;">
                                <?php echo htmlspecialchars($task['description']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($task['assigned_username'] ?? 'ไม่ระบุ'); ?></td>
                            <td><?php echo htmlspecialchars($task['creator_username'] ?? 'ไม่ระบุ'); ?></td>
                            <td>
                                <?php 
                                $statusClass = match($task['status']) {
                                    'completed' => 'success',
                                    'in_progress' => 'warning',
                                    'pending' => 'secondary',
                                    default => 'secondary'
                                };
                                $statusText = match($task['status']) {
                                    'completed' => 'เสร็จสิ้น',
                                    'in_progress' => 'กำลังดำเนินการ',
                                    'pending' => 'รอดำเนินการ',
                                    default => 'รอดำเนินการ'
                                };
                                ?>
                                <span class="badge text-bg-<?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $priorityClass = match($task['priority']) {
                                    'high' => 'danger',
                                    'medium' => 'warning',
                                    'low' => 'info',
                                    default => 'secondary'
                                };
                                $priorityText = match($task['priority']) {
                                    'high' => 'สูง',
                                    'medium' => 'ปานกลาง',
                                    'low' => 'ต่ำ',
                                    default => 'ไม่ระบุ'
                                };
                                ?>
                                <span class="badge text-bg-<?php echo $priorityClass; ?>">
                                    <?php echo $priorityText; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($task['due_date'])); ?></td>
                            <td>
                                <a href="edit_task.php?id=<?php echo $task['task_id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDelete(<?php echo $task['task_id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มงาน -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มงานใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="add_task.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">หัวข้อ</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ผู้รับผิดชอบ</label>
                        <select class="form-select" name="assigned_to">
                            <option value="">เลือกผู้รับผิดชอบ</option>
                            <?php
                            $users = $conn->query("SELECT user_id, username FROM users WHERE is_active = 1");
                            while ($user = $users->fetch_assoc()) {
                                echo "<option value='{$user['user_id']}'>{$user['username']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ความสำคัญ</label>
                        <select class="form-select" name="priority" required>
                            <option value="low">ต่ำ</option>
                            <option value="medium" selected>ปานกลาง</option>
                            <option value="high">สูง</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">กำหนดส่ง</label>
                        <input type="date" class="form-control" name="due_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(taskId) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบงานนี้?')) {
        window.location.href = 'delete_task.php?id=' + taskId;
    }
}
</script>

<?php include '../footer.php'; ?> 