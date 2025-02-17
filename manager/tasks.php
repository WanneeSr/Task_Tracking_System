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

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800">จัดการงาน</h2>
        <button type="button" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md transition duration-200 flex items-center space-x-2"
                data-bs-toggle="modal" 
                data-bs-target="#addTaskModal">
            <i class="bi bi-plus-lg"></i>
            <span>เพิ่มงานใหม่</span>
        </button>
    </div>

    <!-- ตารางแสดงงาน -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หัวข้อ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้รับผิดชอบ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ความสำคัญ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">กำหนดส่ง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($tasks->num_rows > 0): ?>
                            <?php while ($task = $tasks->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($task['assigned_username'] ?? 'ยังไม่ได้มอบหมาย'); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                            echo match($task['status']) {
                                                'completed' => 'bg-green-100 text-green-800',
                                                'in_progress' => 'bg-yellow-100 text-yellow-800',
                                                'pending' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                            echo match($task['priority']) {
                                                'high' => 'bg-red-100 text-red-800',
                                                'medium' => 'bg-yellow-100 text-yellow-800',
                                                'low' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-gray-100 text-gray-800'
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="/task_tracking_system/manager/view_task.php?id=<?php echo $task['task_id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/task_tracking_system/manager/edit_task.php?id=<?php echo $task['task_id']; ?>" 
                                           class="text-yellow-600 hover:text-yellow-900">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="/task_tracking_system/manager/delete_task.php?id=<?php echo $task['task_id']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบงานนี้?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">ไม่พบรายการงาน</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มงานใหม่ -->
<div class="modal fade" id="addTaskModal" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-dialog-centered fixed top-0 left-1/2 transform -translate-x-1/2 mt-8" style="max-width: 600px;">
        <div class="modal-content rounded-xl shadow-2xl border-0">
            <div class="modal-header bg-gradient-to-r from-blue-500 to-blue-600 text-white border-none px-6 py-4 rounded-t-xl">
                <h5 class="text-xl font-semibold">เพิ่มงานใหม่</h5>
                <button type="button" class="text-white hover:text-gray-200 transition duration-200" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body p-6 bg-gray-50">
                <form id="addTaskForm" action="/task_tracking_system/manager/add_task.php" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">หัวข้องาน</label>
                            <input type="text" 
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200" 
                                   id="title" 
                                   name="title" 
                                   required>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">รายละเอียด</label>
                            <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200" 
                                      id="description" 
                                      name="description" 
                                      rows="3"></textarea>
                        </div>
                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">มอบหมายให้</label>
                            <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200" 
                                    id="assigned_to" 
                                    name="assigned_to">
                                <option value="">เลือกผู้รับผิดชอบ</option>
                                <?php 
                                $employees->data_seek(0);
                                while ($employee = $employees->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $employee['user_id']; ?>">
                                        <?php echo htmlspecialchars($employee['username'] . ' (' . $employee['email'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">ความสำคัญ</label>
                                <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200" 
                                        id="priority" 
                                        name="priority" 
                                        required>
                                    <option value="low">ต่ำ</option>
                                    <option value="medium">ปานกลาง</option>
                                    <option value="high">สูง</option>
                                </select>
                            </div>
                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">กำหนดส่ง</label>
                                <input type="date" 
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200" 
                                       id="due_date" 
                                       name="due_date" 
                                       required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-gray-50 border-t border-gray-200 px-6 py-4 rounded-b-xl">
                <button type="button" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 shadow-sm transition duration-200" 
                        data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" 
                        form="addTaskForm" 
                        class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-lg shadow-sm transition duration-200 ml-2">
                        บันทึก
                </button>
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

// เพิ่ม JavaScript เพื่อจัดการการแสดง Modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addTaskModal');
    const modalTrigger = document.querySelector('[data-bs-target="#addTaskModal"]');
    const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');

    // ซ่อน Modal เมื่อโหลดหน้าเว็บ
    modal.style.display = 'none';

    // แสดง Modal เมื่อกดปุ่ม
    modalTrigger.addEventListener('click', function() {
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
        modal.classList.add('show');
    });

    // ซ่อน Modal เมื่อกดปุ่มปิด
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            modal.classList.remove('show');
        });
    });

    // ซ่อน Modal เมื่อคลิกพื้นหลัง
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            modal.classList.remove('show');
        }
    });
});
</script>

<?php include '../footer.php'; ?> 