<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// ตรวจสอบ ID ที่ส่งมา
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['errors'] = ["ไม่พบรหัสงานที่ต้องการแก้ไข"];
    header("Location: tasks.php");
    exit();
}

$task_id = $_GET['id'];

// ดึงข้อมูลงานที่ต้องการแก้ไข
$stmt = $conn->prepare("SELECT * FROM tasks WHERE task_id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();

if (!$task) {
    $_SESSION['errors'] = ["ไม่พบข้อมูลงานที่ต้องการแก้ไข"];
    header("Location: tasks.php");
    exit();
}

// ดึงรายชื่อผู้ใช้สำหรับ dropdown
$users = $conn->query("SELECT user_id, username FROM users WHERE is_active = 1");
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">แก้ไขงาน</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="update_task.php">
                <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                
                <div class="mb-3">
                    <label class="form-label">หัวข้อ</label>
                    <input type="text" class="form-control" name="title" 
                           value="<?php echo htmlspecialchars($task['title']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">รายละเอียด</label>
                    <textarea class="form-control" name="description" rows="3"><?php 
                        echo htmlspecialchars($task['description']); 
                    ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">ผู้รับผิดชอบ</label>
                    <select class="form-select" name="assigned_to">
                        <option value="">เลือกผู้รับผิดชอบ</option>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <option value="<?php echo $user['user_id']; ?>" 
                                <?php echo ($user['user_id'] == $task['assigned_to']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">สถานะ</label>
                    <select class="form-select" name="status" required>
                        <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>
                            รอดำเนินการ
                        </option>
                        <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>
                            กำลังดำเนินการ
                        </option>
                        <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>
                            เสร็จสิ้น
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">ความสำคัญ</label>
                    <select class="form-select" name="priority" required>
                        <option value="low" <?php echo $task['priority'] == 'low' ? 'selected' : ''; ?>>ต่ำ</option>
                        <option value="medium" <?php echo $task['priority'] == 'medium' ? 'selected' : ''; ?>>ปานกลาง</option>
                        <option value="high" <?php echo $task['priority'] == 'high' ? 'selected' : ''; ?>>สูง</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">กำหนดส่ง</label>
                    <input type="date" class="form-control" name="due_date" 
                           value="<?php echo $task['due_date']; ?>" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="tasks.php" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 