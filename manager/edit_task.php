<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkManager();

// ตรวจสอบว่ามี task_id
if (!isset($_GET['id'])) {
    header('Location: tasks.php');
    exit();
}

$task_id = $_GET['id'];
$manager_id = $_SESSION['user_id'];

// ดึงข้อมูลงาน
$sql = "SELECT t.*, u.username as assigned_username 
        FROM tasks t 
        LEFT JOIN users u ON t.assigned_to = u.user_id 
        WHERE t.task_id = ? AND t.created_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $task_id, $manager_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();

if (!$task) {
    $_SESSION['error'] = "ไม่พบงานที่ระบุ หรือคุณไม่มีสิทธิ์แก้ไขงานนี้";
    header('Location: tasks.php');
    exit();
}

// ดึงรายชื่อพนักงาน
$employees_sql = "SELECT user_id, username, email FROM users WHERE role = 'employee'";
$employees = $conn->query($employees_sql);

// จัดการการอัพเดทข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    if (empty($title) || empty($due_date)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {
        $update_sql = "UPDATE tasks 
                      SET title = ?, description = ?, assigned_to = ?, 
                          priority = ?, due_date = ?, status = ? 
                      WHERE task_id = ? AND created_by = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssisssii", $title, $description, $assigned_to, 
                         $priority, $due_date, $status, $task_id, $manager_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "อัพเดทงานเรียบร้อยแล้ว";
            header('Location: tasks.php');
            exit();
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $conn->error;
        }
    }
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">แก้ไขงาน</h5>
            <a href="tasks.php" class="btn btn-secondary btn-sm">กลับ</a>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="title" class="form-label">หัวข้องาน</label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?php echo htmlspecialchars($task['title']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">รายละเอียด</label>
                    <textarea class="form-control" id="description" name="description" 
                              rows="3"><?php echo htmlspecialchars($task['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="assigned_to" class="form-label">มอบหมายให้</label>
                    <select class="form-select" id="assigned_to" name="assigned_to">
                        <option value="">เลือกผู้รับผิดชอบ</option>
                        <?php while ($employee = $employees->fetch_assoc()): ?>
                            <option value="<?php echo $employee['user_id']; ?>" 
                                    <?php echo ($task['assigned_to'] == $employee['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($employee['username'] . ' (' . $employee['email'] . ')'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="priority" class="form-label">ความสำคัญ</label>
                        <select class="form-select" id="priority" name="priority" required>
                            <option value="low" <?php echo ($task['priority'] == 'low') ? 'selected' : ''; ?>>ต่ำ</option>
                            <option value="medium" <?php echo ($task['priority'] == 'medium') ? 'selected' : ''; ?>>ปานกลาง</option>
                            <option value="high" <?php echo ($task['priority'] == 'high') ? 'selected' : ''; ?>>สูง</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">สถานะ</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" <?php echo ($task['status'] == 'pending') ? 'selected' : ''; ?>>รอดำเนินการ</option>
                            <option value="in_progress" <?php echo ($task['status'] == 'in_progress') ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                            <option value="completed" <?php echo ($task['status'] == 'completed') ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="due_date" class="form-label">กำหนดส่ง</label>
                        <input type="date" class="form-control" id="due_date" name="due_date"
                               value="<?php echo $task['due_date']; ?>" required>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>