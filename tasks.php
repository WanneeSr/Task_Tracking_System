<?php
include 'header.php';
require_once 'db.php';
require_once 'auth.php';

// เพิ่มโค้ดสำหรับบันทึกงาน
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_task') {
    try {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $priority = $_POST['priority'];
        $due_date = $_POST['due_date'];
        $assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
        $created_by = $_SESSION['user_id'];

        $sql = "INSERT INTO tasks (title, description, priority, due_date, assigned_to, created_by) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $title, $description, $priority, $due_date, $assigned_to, $created_by);

        if ($stmt->execute()) {
            $_SESSION['success'] = "บันทึกงานสำเร็จ";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $stmt->error;
        }
        
        // Redirect เพื่อป้องกันการ submit ซ้ำ
        header("Location: tasks.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// ดึงข้อมูลงานตามบทบาทของผู้ใช้
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$tasks_sql = match($role) {
    'admin' => "SELECT t.*, u.username as assigned_username 
               FROM tasks t 
               LEFT JOIN users u ON t.assigned_to = u.user_id 
               ORDER BY t.created_at DESC",
    'manager' => "SELECT t.*, u.username as assigned_username 
                 FROM tasks t 
                 LEFT JOIN users u ON t.assigned_to = u.user_id 
                 WHERE t.created_by = ? 
                 ORDER BY t.created_at DESC",
    'employee' => "SELECT t.*, u.username as assigned_username 
                  FROM tasks t 
                  LEFT JOIN users u ON t.assigned_to = u.user_id 
                  WHERE t.assigned_to = ? 
                  ORDER BY t.created_at DESC",
    default => null
};

if ($tasks_sql) {
    $stmt = $conn->prepare($tasks_sql);
    if ($role !== 'admin') {
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<div class="container mt-4">
    <!-- แสดงข้อความแจ้งเตือน -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>รายการงาน</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="bi bi-plus-lg"></i> เพิ่มงานใหม่
        </button>
    </div>

    <!-- ตัวกรองงาน -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">สถานะ</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">ทั้งหมด</option>
                        <option value="pending">รอดำเนินการ</option>
                        <option value="in_progress">กำลังดำเนินการ</option>
                        <option value="completed">เสร็จสิ้น</option>
                        <option value="cancelled">ยกเลิก</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ความสำคัญ</label>
                    <select class="form-select" id="filterPriority">
                        <option value="">ทั้งหมด</option>
                        <option value="low">ต่ำ</option>
                        <option value="medium">ปานกลาง</option>
                        <option value="high">สูง</option>
                        <option value="urgent">เร่งด่วน</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">มอบหมายให้</label>
                    <select class="form-select" id="filterAssignee">
                        <option value="">ทั้งหมด</option>
                        <?php
                        $users = $conn->query("SELECT user_id, username FROM users");
                        while ($user = $users->fetch_assoc()) {
                            echo "<option value='{$user['user_id']}'>{$user['username']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">กำหนดส่ง</label>
                    <input type="date" class="form-control" id="filterDueDate">
                </div>
            </form>
        </div>
    </div>

    <!-- รายการงาน -->
    <div class="row" id="taskList">
        <?php if (isset($result) && $result->num_rows > 0): ?>
            <?php while ($task = $result->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <?php echo htmlspecialchars($task['title']); ?>
                            </h5>
                            <span class="badge bg-<?php 
                                echo match($task['priority']) {
                                    'low' => 'success',
                                    'medium' => 'info',
                                    'high' => 'warning',
                                    'urgent' => 'danger'
                                };
                            ?>">
                                <?php echo htmlspecialchars($task['priority']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    มอบหมายให้: <?php echo htmlspecialchars($task['assigned_username'] ?? 'ไม่ระบุ'); ?>
                                </small>
                                <small class="text-muted">
                                    กำหนดส่ง: <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-<?php 
                                    echo match($task['status']) {
                                        'pending' => 'secondary',
                                        'in_progress' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    };
                                ?>">
                                    <?php echo htmlspecialchars($task['status']); ?>
                                </span>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="viewTask(<?php echo $task['task_id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    <?php if (canEditTask($task['task_id'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                            onclick="updateStatus(<?php echo $task['task_id']; ?>)">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if (canDeleteTask($task['task_id'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteTask(<?php echo $task['task_id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="card-text text-center">ไม่พบรายการงาน</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
            <form method="POST" action="tasks.php">
                <input type="hidden" name="action" value="add_task">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">หัวข้องาน</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">มอบหมายให้</label>
                        <select class="form-select" name="assigned_to">
                            <option value="">เลือกผู้รับผิดชอบ</option>
                            <?php
                            $users = $conn->query("SELECT user_id, username FROM users");
                            while ($user = $users->fetch_assoc()) {
                                echo "<option value='{$user['user_id']}'>{$user['username']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ความสำคัญ</label>
                            <select class="form-select" name="priority" required>
                                <option value="low">ต่ำ</option>
                                <option value="medium" selected>ปานกลาง</option>
                                <option value="high">สูง</option>
                                <option value="urgent">เร่งด่วน</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">กำหนดส่ง</label>
                            <input type="date" class="form-control" name="due_date" required>
                        </div>
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

<?php include 'footer.php'; ?>

<script>
// เพิ่ม JavaScript สำหรับจัดการงาน
function saveTask() {
    // โค้ดสำหรับบันทึกงานใหม่
}

function viewTask(taskId) {
    // โค้ดสำหรับดูรายละเอียดงาน
}

function updateStatus(taskId) {
    // โค้ดสำหรับอัพเดตสถานะงาน
}

function deleteTask(taskId) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบงานนี้?')) {
        window.location.href = `delete_task.php?id=${taskId}`;
    }
}

// ตัวกรองงาน
document.querySelectorAll('#filterStatus, #filterPriority, #filterAssignee, #filterDueDate')
    .forEach(element => {
        element.addEventListener('change', filterTasks);
    });

function filterTasks() {
    // โค้ดสำหรับกรองงาน
}
</script> 