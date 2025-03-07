<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
checkLogin();

// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// ตรวจสอบบทบาทและนำทางไปยังหน้าที่เหมาะสม
switch ($_SESSION['role']) {
    case 'admin':
        header('Location: admin/dashboard.php');
        exit();
    case 'manager':
        header('Location: manager/dashboard.php');
        exit();
}

// เพิ่มโค้ดสำหรับบันทึกงาน
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_task') {
    try {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $priority = $_POST['priority'];
        $due_date = $_POST['due_date'];
        $assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
        $created_by = $_SESSION['user_id'];
        $status = 'pending';

        $sql = "INSERT INTO tasks (title, description, priority, due_date, assigned_to, created_by, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssiss", $title, $description, $priority, $due_date, $assigned_to, $created_by, $status);

        if ($stmt->execute()) {
            // ดึงข้อมูลงานที่เพิ่งเพิ่มเข้าไป
            $new_task_id = $mysqli->insert_id;
            $fetch_sql = "SELECT t.*, 
                         u.username as assigned_username,
                         DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') as created_at_formatted
                         FROM tasks t 
                         LEFT JOIN users u ON t.assigned_to = u.user_id 
                         WHERE t.task_id = ?";
            
            $fetch_stmt = $mysqli->prepare($fetch_sql);
            $fetch_stmt->bind_param("i", $new_task_id);
            $fetch_stmt->execute();
            $new_task = $fetch_stmt->get_result()->fetch_assoc();

            if ($new_task) {
                $_SESSION['new_task'] = $new_task;
            }
            $_SESSION['success'] = "บันทึกงานสำเร็จ";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $stmt->error;
        }
        
        header("Location: tasks.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: tasks.php");
        exit();
    }
}

// ดึงข้อมูลงานตามบทบาทของผู้ใช้
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$tasks_sql = match($role) {
    'admin' => "SELECT t.*, 
               u.username as assigned_username,
               DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') as created_at_formatted
               FROM tasks t 
               LEFT JOIN users u ON t.assigned_to = u.user_id 
               ORDER BY t.created_at DESC",
    'manager' => "SELECT t.*, 
                 u.username as assigned_username,
                 DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') as created_at_formatted
                 FROM tasks t 
                 LEFT JOIN users u ON t.assigned_to = u.user_id 
                 WHERE t.created_by = ? 
                 ORDER BY t.created_at DESC",
    'employee' => "SELECT t.*, 
                  u.username as assigned_username,
                  DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') as created_at_formatted
                  FROM tasks t 
                  LEFT JOIN users u ON t.assigned_to = u.user_id 
                  WHERE t.assigned_to = ? 
                  ORDER BY t.created_at DESC",
    default => null
};

if ($tasks_sql) {
    $stmt = $mysqli->prepare($tasks_sql);
    if ($role !== 'admin') {
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
}

// กำหนดตัวแปร $current_page สำหรับ active menu
$current_page = basename($_SERVER['PHP_SELF']);

include 'header.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการงาน</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gray-50">

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">รายการงาน</h1>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- สถานะ -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                        <select id="filterStatus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">ทั้งหมด</option>
                            <option value="pending">รอดำเนินการ</option>
                            <option value="in_progress">กำลังดำเนินการ</option>
                            <option value="completed">เสร็จสิ้น</option>
                            <option value="cancelled">ยกเลิก</option>
                        </select>
                    </div>

                    <!-- ความสำคัญ -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ความสำคัญ</label>
                        <select id="filterPriority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">ทั้งหมด</option>
                            <option value="low">ต่ำ</option>
                            <option value="medium">ปานกลาง</option>
                            <option value="high">สูง</option>
                            <option value="urgent">เร่งด่วน</option>
                        </select>
                    </div>

                    <!-- มอบหมายให้ -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">มอบหมายให้</label>
                        <select id="filterAssignee" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">ทั้งหมด</option>
                            <?php
                            $users = $mysqli->query("SELECT user_id, username FROM users");
                            while ($user = $users->fetch_assoc()) {
                                echo "<option value='{$user['user_id']}'>{$user['username']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- กำหนดส่ง -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">กำหนดส่ง</label>
                        <input type="date" id="filterDueDate" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Tasks Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="taskList">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($task = $result->fetch_assoc()): ?>
                        <div class="task-card bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow" 
                             data-task-id="<?php echo $task['task_id']; ?>"
                             data-status="<?php echo $task['status']; ?>"
                             data-priority="<?php echo $task['priority']; ?>"
                             data-assignee="<?php echo $task['assigned_to']; ?>"
                             data-due-date="<?php echo date('Y-m-d', strtotime($task['due_date'])); ?>">
                            <!-- Task Header -->
                            <div class="px-4 py-3 border-b flex justify-between items-center">
                                <h3 class="font-semibold text-gray-900 truncate">
                                    <?php echo htmlspecialchars($task['title']); ?>
                                </h3>
                            </div>

                            <!-- Task Body -->
                            <div class="p-4">
                                <p class="text-gray-600 text-sm mb-4">
                                    <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                                </p>
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="bi bi-person mr-2"></i>
                                        <?php echo htmlspecialchars($task['assigned_username'] ?? 'ไม่ระบุ'); ?>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="bi bi-calendar mr-2"></i>
                                        <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="bi bi-clock mr-2"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($task['created_at'])); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Task Footer -->
                            <div class="px-4 py-3 bg-gray-50 border-t rounded-b-lg">
                                <div class="flex justify-between items-center">
                                <div class="flex space-x-2">
                                    <span class="status-badge px-2 py-1 text-xs font-medium rounded-full 
                                        <?php
                                        echo match($task['status']) {
                                            'pending' => 'bg-gray-100 text-gray-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        ?>">
                                        <?php
                                        echo match($task['status']) {
                                            'pending' => 'รอดำเนินการ',
                                            'in_progress' => 'กำลังดำเนินการ',
                                            'completed' => 'เสร็จสิ้น',
                                            'cancelled' => 'ยกเลิก',
                                            default => 'ไม่ระบุ'
                                        };
                                        ?>
                                    </span>
                                    <span class="priority-badge px-2 py-1 text-xs font-medium rounded-full 
                                        <?php
                                        echo match($task['priority']) {
                                            'low' => 'bg-green-100 text-green-800',
                                            'medium' => 'bg-blue-100 text-blue-800',
                                            'high' => 'bg-yellow-100 text-yellow-800',
                                            'urgent' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        ?>">
                                        <?php
                                        echo match($task['priority']) {
                                            'low' => 'ต่ำ',
                                            'medium' => 'ปานกลาง',
                                            'high' => 'สูง',
                                            'urgent' => 'เร่งด่วน',
                                            default => 'ไม่ระบุ'
                                        };
                                        ?>
                                    </span>
                                </div>
                                    <div class="flex space-x-2">
                                        <button onclick="viewTask(<?php echo $task['task_id']; ?>)"
                                                class="p-1 text-gray-500 hover:text-indigo-600 transition-colors">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (canEditTask($task['task_id'])): ?>
                                        <button onclick="updateStatus(<?php echo $task['task_id']; ?>)"
                                                class="p-1 text-gray-500 hover:text-green-600 transition-colors">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (canDeleteTask($task['task_id'])): ?>
                                        <button onclick="deleteTask(<?php echo $task['task_id']; ?>)"
                                                class="p-1 text-gray-500 hover:text-red-600 transition-colors">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">ไม่พบรายการงาน</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    

    <script>
    // ฟังก์ชันสำหรับกรองงาน
    function filterTasks() {
        const status = document.getElementById('filterStatus').value;
        const priority = document.getElementById('filterPriority').value;
        const assignee = document.getElementById('filterAssignee').value;
        const dueDate = document.getElementById('filterDueDate').value;
        const taskCards = document.querySelectorAll('.task-card');

        // ลบข้อความ "ไม่พบข้อมูล" เดิม (ถ้ามี)
        const existingNoData = document.getElementById('noDataMessage');
        if (existingNoData) {
            existingNoData.remove();
        }

        let visibleCount = 0;

        taskCards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            const cardPriority = card.getAttribute('data-priority');
            const cardAssignee = card.getAttribute('data-assignee');
            const cardDueDate = card.getAttribute('data-due-date');
            
            // ตรวจสอบเงื่อนไขทั้งหมด
            const statusMatch = !status || cardStatus === status;
            const priorityMatch = !priority || cardPriority === priority;
            const assigneeMatch = !assignee || cardAssignee === assignee;
            const dueDateMatch = !dueDate || cardDueDate === dueDate;

            // แสดงการ์ดเมื่อตรงตามเงื่อนไขทั้งหมด
            if (statusMatch && priorityMatch && assigneeMatch && dueDateMatch) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // แสดงข้อความเมื่อไม่พบข้อมูล
        if (visibleCount === 0) {
            const noDataMessage = `
                <div id="noDataMessage" class="col-span-full">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">ไม่พบรายการงานที่ตรงกับเงื่อนไขการค้นหา</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('taskList').insertAdjacentHTML('beforeend', noDataMessage);
        }
    }

    // เพิ่ม Event Listener เมื่อโหลดหน้าเสร็จ
    document.addEventListener('DOMContentLoaded', function() {
        // เพิ่ม event listener สำหรับทุกตัวกรอง
        const filterStatus = document.getElementById('filterStatus');
        const filterPriority = document.getElementById('filterPriority');
        const filterAssignee = document.getElementById('filterAssignee');
        const filterDueDate = document.getElementById('filterDueDate');
        
        if (filterStatus) filterStatus.addEventListener('change', filterTasks);
        if (filterPriority) filterPriority.addEventListener('change', filterTasks);
        if (filterAssignee) filterAssignee.addEventListener('change', filterTasks);
        if (filterDueDate) filterDueDate.addEventListener('change', filterTasks);
    });

    // เพิ่ม JavaScript สำหรับจัดการงาน
    function saveTask() {
        // โค้ดสำหรับบันทึกงานใหม่
    }

    // เพิ่มฟังก์ชัน viewTask
    function viewTask(taskId) {
        fetch(`get_task_details.php?id=${taskId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const task = data.task;
                    const modalContent = `
                        <div id="viewTaskModal" 
                             class="fixed inset-0 z-50 overflow-y-auto"
                             aria-labelledby="modal-title" 
                             role="dialog" 
                             aria-modal="true">
                            <!-- Background backdrop -->
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 backdrop-blur-sm transition-opacity"></div>

                            <!-- Modal panel -->
                            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                                    <!-- Header -->
                                    <div class="bg-gradient-to-r from-blue-600 to-indigo-500 px-4 py-3">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-lg font-medium text-white">รายละเอียดงาน</h3>
                                            <button type="button" 
                                                    class="text-white hover:text-gray-200 focus:outline-none"
                                                    onclick="closeViewModal()">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="bg-white px-4 py-5 sm:p-6">
                                        <div class="space-y-4">
                                            <!-- ชื่องาน -->
                                            <div class="border-b pb-3">
                                                <h4 class="text-xl font-semibold text-gray-900">${task.title}</h4>
                                            </div>

                                            <!-- รายละเอียด -->
                                            <div class="py-3">
                                                <p class="text-gray-700 whitespace-pre-line">${task.description}</p>
                                            </div>

                                            <!-- ข้อมูลเพิ่มเติม -->
                                            <div class="grid grid-cols-2 gap-4 py-3">
                                                <div>
                                                    <p class="text-sm text-gray-500">สถานะ</p>
                                                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full
                                                        ${task.status === 'pending' ? 'bg-gray-100 text-gray-800' :
                                                          task.status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                                          task.status === 'completed' ? 'bg-green-100 text-green-800' :
                                                          'bg-red-100 text-red-800'}">
                                                        ${task.status === 'pending' ? 'รอดำเนินการ' :
                                                          task.status === 'in_progress' ? 'กำลังดำเนินการ' :
                                                          task.status === 'completed' ? 'เสร็จสิ้น' : 'ยกเลิก'}
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-500">ความสำคัญ</p>
                                                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full
                                                        ${task.priority === 'low' ? 'bg-green-100 text-green-800' :
                                                          task.priority === 'medium' ? 'bg-blue-100 text-blue-800' :
                                                          task.priority === 'high' ? 'bg-yellow-100 text-yellow-800' :
                                                          'bg-red-100 text-red-800'}">
                                                        ${task.priority_text}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4 border-t pt-3">
                                                <div>
                                                    <p class="text-sm text-gray-500">ผู้รับผิดชอบ</p>
                                                    <p class="mt-1 text-sm text-gray-900">
                                                        <i class="bi bi-person mr-1"></i>
                                                        ${task.assigned_username || 'ไม่ระบุ'}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-500">ผู้สร้าง</p>
                                                    <p class="mt-1 text-sm text-gray-900">
                                                        <i class="bi bi-person-plus mr-1"></i>
                                                        ${task.created_username || 'ไม่ระบุ'}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4 border-t pt-3">
                                                <div>
                                                    <p class="text-sm text-gray-500">วันที่สร้าง</p>
                                                    <p class="mt-1 text-sm text-gray-900">
                                                        <i class="bi bi-calendar mr-1"></i>
                                                        ${task.created_at_formatted}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-500">กำหนดส่ง</p>
                                                    <p class="mt-1 text-sm text-gray-900">
                                                        <i class="bi bi-calendar-check mr-1"></i>
                                                        ${task.due_date_formatted}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                        <button type="button"
                                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:w-auto sm:text-sm"
                                                onclick="closeViewModal()">
                                            ปิด
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    // ลบ modal เก่าถ้ามี
                    const existingModal = document.getElementById('viewTaskModal');
                    if (existingModal) {
                        existingModal.remove();
                    }

                    // เพิ่ม modal ใหม่
                    document.body.insertAdjacentHTML('beforeend', modalContent);
                } else {
                    alert(data.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล');
                }
            })
            .catch(error => {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
    }

    // เพิ่มฟังก์ชันปิด modal
    function closeViewModal() {
        const modal = document.getElementById('viewTaskModal');
        if (modal) {
            modal.remove();
        }
    }

    // ปรับปรุงฟังก์ชัน updateStatus
    function updateStatus(taskId) {
        if (confirm('ต้องการเปลี่ยนสถานะงานเป็นเสร็จสิ้นใช่หรือไม่?')) {
            fetch('update_task_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    task_id: taskId,
                    status: 'completed'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // แสดง alert แจ้งสำเร็จ
                    alert('อัพเดทสถานะงานสำเร็จ');
                    
                    // รีเฟรชหน้าเว็บ
                    window.location.reload();
                } else {
                    alert(data.message || 'เกิดข้อผิดพลาดในการอัพเดทสถานะ');
                }
            })
            .catch(error => {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
        }
    }

    // ปรับปรุงฟังก์ชัน deleteTask
    function deleteTask(taskId) {
        if (confirm('คุณแน่ใจหรือไม่ที่จะลบงานนี้?')) {
            fetch(`delete_task.php?id=${taskId}`, {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // แสดง alert แจ้งสำเร็จ
                    alert('ลบงานสำเร็จ');
                    // รีเฟรชหน้าเว็บ
                    window.location.reload();
                } else {
                    alert(data.message || 'เกิดข้อผิดพลาดในการลบงาน');
                }
            })
            .catch(error => {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
        }
    }
    </script>
</body>
</html>