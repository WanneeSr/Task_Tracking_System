<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// ดึงรายการพนักงานทั้งหมด
$employees_sql = "SELECT user_id, username, email FROM users WHERE role = 'employee'";
$employees = $conn->query($employees_sql);

// ดึงรายการงานทั้งหมด
$tasks_sql = "
    SELECT t.*, u.username as assigned_username
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.user_id
    ORDER BY t.created_at DESC";
$tasks = $conn->query($tasks_sql);
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <!-- หัวข้อและปุ่มเพิ่มงาน -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">จัดการงาน</h2>
                <p class="text-gray-600 mt-2">จัดการและติดตามงานทั้งหมดในระบบ</p>
            </div>
            <button type="button" 
                    class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-lg shadow-lg transition duration-200 flex items-center space-x-2 transform hover:scale-105"
                    data-bs-toggle="modal" 
                    data-bs-target="#addTaskModal">
                <i class="bi bi-plus-lg"></i>
                <span>เพิ่มงานใหม่</span>
            </button>
        </div>

        <!-- แสดงงานในรูปแบบการ์ด -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($task = $tasks->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <!-- ส่วนหัวการ์ด -->
                    <div class="px-6 py-4 border-b border-gray-100">
                        <div class="flex justify-between items-start">
                            <h3 class="text-lg font-semibold text-gray-800 truncate">
                                <?php echo htmlspecialchars($task['title']); ?>
                            </h3>
                            <div class="flex space-x-2">
                                <button onclick="viewTask(<?php echo $task['task_id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-900 transition duration-150">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button onclick="editTask(<?php echo $task['task_id']; ?>)" 
                                        class="text-yellow-600 hover:text-yellow-900 transition duration-150">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="deleteTask(<?php echo $task['task_id']; ?>)" 
                                        class="text-red-600 hover:text-red-900 transition duration-150">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- เนื้อหาการ์ด -->
                    <div class="px-6 py-4">
                        <p class="text-gray-600 text-sm mb-4">
                            <?php echo htmlspecialchars($task['description']); ?>
                        </p>
                        
                        <!-- ผู้รับผิดชอบ -->
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="bi bi-person text-gray-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-gray-900">
                                    <?php echo htmlspecialchars($task['assigned_username'] ?? 'ไม่ระบุ'); ?>
                                </p>
                            </div>
                        </div>

                        <!-- สถานะและความสำคัญ -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php 
                                echo match($task['status']) {
                                    'completed' => 'bg-green-100 text-green-800',
                                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                                    'pending' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <i class="bi bi-circle-fill text-xs mr-1"></i>
                                <?php 
                                echo match($task['status']) {
                                    'completed' => 'เสร็จสิ้น',
                                    'in_progress' => 'กำลังดำเนินการ',
                                    'pending' => 'รอดำเนินการ',
                                    default => 'ไม่ระบุ'
                                };
                                ?>
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php 
                                echo match($task['priority']) {
                                    'high' => 'bg-red-100 text-red-800',
                                    'medium' => 'bg-yellow-100 text-yellow-800',
                                    'low' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <i class="bi bi-flag-fill text-xs mr-1"></i>
                                <?php 
                                echo match($task['priority']) {
                                    'high' => 'ความสำคัญสูง',
                                    'medium' => 'ความสำคัญปานกลาง',
                                    'low' => 'ความสำคัญต่ำ',
                                    default => 'ไม่ระบุ'
                                };
                                ?>
                            </span>
                        </div>

                        <!-- กำหนดส่ง -->
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="bi bi-calendar mr-2"></i>
                            <span>กำหนดส่ง: <?php echo date('d/m/Y', strtotime($task['due_date'])); ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Modal เพิ่มงานใหม่ -->
<div class="modal fade" id="addTaskModal" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-dialog-centered fixed top-0 left-1/2 transform -translate-x-1/2 mt-8" style="max-width: 800px; width: 90%;">
        <div class="modal-content rounded-xl shadow-2xl border-0">
            <div class="modal-header bg-gradient-to-r from-blue-500 to-blue-600 text-white border-none px-8 py-5 rounded-t-xl">
                <h5 class="text-2xl font-semibold">เพิ่มงานใหม่</h5>
                <button type="button" class="text-white hover:text-gray-200 transition duration-200" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
            <div class="modal-body p-8 bg-gray-50">
                <form id="addTaskForm" action="/task_tracking_system/admin/add_task.php" method="POST">
                    <div class="space-y-6">
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
                                <?php while ($employee = $employees->fetch_assoc()): ?>
                                    <option value="<?php echo $employee['user_id']; ?>">
                                        <?php echo htmlspecialchars($employee['username']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                            <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="pending">รอดำเนินการ</option>
                                <option value="in_progress">กำลังดำเนินการ</option>
                                <option value="completed">เสร็จสิ้น</option>
                            </select>
                        </div>
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
                </form>
            </div>
            <div class="modal-footer bg-gray-50 border-t border-gray-200 px-8 py-5 rounded-b-xl">
                <button type="button" 
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 shadow-sm transition duration-200" 
                        data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" 
                        form="addTaskForm" 
                        class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-lg shadow-sm transition duration-200 ml-3">
                        บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- เพิ่ม Modal สำหรับดูรายละเอียดงาน -->
<div id="viewTaskModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop with blur effect -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm"></div>
    
    <!-- Modal Content -->
    <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl">
        <div class="bg-white rounded-xl shadow-2xl">
            <!-- Modal Header -->
            <div class="px-8 py-5 bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-semibold text-white" id="viewTaskTitle"></h3>
                    <button type="button" onclick="closeViewModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-8 bg-gray-50">
                <!-- รายละเอียดงาน -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">รายละเอียดงาน</h4>
                    <p class="text-gray-800 whitespace-pre-line" id="viewTaskDescription"></p>
                </div>

                <!-- ข้อมูลเพิ่มเติม -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- ผู้รับผิดชอบ -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">ผู้รับผิดชอบ</h4>
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="bi bi-person text-blue-600"></i>
                            </div>
                            <span class="ml-3 text-gray-800" id="viewTaskAssignee"></span>
                        </div>
                    </div>

                    <!-- กำหนดส่ง -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">กำหนดส่ง</h4>
                        <div class="flex items-center text-gray-800">
                            <i class="bi bi-calendar text-blue-600 mr-2"></i>
                            <span id="viewTaskDueDate"></span>
                        </div>
                    </div>

                    <!-- สถานะ -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">สถานะ</h4>
                        <span id="viewTaskStatus" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"></span>
                    </div>

                    <!-- ความสำคัญ -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">ความสำคัญ</h4>
                        <span id="viewTaskPriority" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"></span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 rounded-b-xl">
                <button type="button" 
                        onclick="closeViewModal()" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-colors duration-200">
                    ปิด
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal แก้ไขงาน -->
<div id="editTaskModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop with blur effect -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm"></div>
    
    <!-- Modal Content -->
    <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl">
        <div class="bg-white rounded-xl shadow-2xl">
            <!-- Modal Header -->
            <div class="px-8 py-5 bg-gradient-to-r from-yellow-500 to-yellow-600">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-semibold text-white">แก้ไขงาน</h3>
                    <button type="button" onclick="closeEditModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-8 bg-gray-50">
                <form id="editTaskForm" onsubmit="updateTask(event)">
                    <input type="hidden" id="editTaskId" name="task_id">
                    <div class="space-y-6">
                        <!-- ชื่องาน -->
                        <div>
                            <label for="editTitle" class="block text-sm font-medium text-gray-700 mb-1">ชื่องาน</label>
                            <input type="text" 
                                   id="editTitle" 
                                   name="title" 
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" 
                                   required>
                        </div>

                        <!-- รายละเอียด -->
                        <div>
                            <label for="editDescription" class="block text-sm font-medium text-gray-700 mb-1">รายละเอียด</label>
                            <textarea id="editDescription" 
                                      name="description" 
                                      rows="4" 
                                      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- ผู้รับผิดชอบ -->
                            <div>
                                <label for="editAssignedTo" class="block text-sm font-medium text-gray-700 mb-1">ผู้รับผิดชอบ</label>
                                <select id="editAssignedTo" 
                                        name="assigned_to" 
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                    <option value="">เลือกผู้รับผิดชอบ</option>
                                    <?php 
                                    $employees = $conn->query("SELECT user_id, username FROM users WHERE role = 'employee'");
                                    while ($employee = $employees->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $employee['user_id']; ?>">
                                            <?php echo htmlspecialchars($employee['username']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- กำหนดส่ง -->
                            <div>
                                <label for="editDueDate" class="block text-sm font-medium text-gray-700 mb-1">กำหนดส่ง</label>
                                <input type="date" 
                                       id="editDueDate" 
                                       name="due_date" 
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" 
                                       required>
                            </div>

                            <!-- สถานะ -->
                            <div>
                                <label for="editStatus" class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                                <select id="editStatus" 
                                        name="status" 
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" 
                                        required>
                                    <option value="pending">รอดำเนินการ</option>
                                    <option value="in_progress">กำลังดำเนินการ</option>
                                    <option value="completed">เสร็จสิ้น</option>
                                </select>
                            </div>

                            <!-- ความสำคัญ -->
                            <div>
                                <label for="editPriority" class="block text-sm font-medium text-gray-700 mb-1">ความสำคัญ</label>
                                <select id="editPriority" 
                                        name="priority" 
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" 
                                        required>
                                    <option value="low">ต่ำ</option>
                                    <option value="medium">ปานกลาง</option>
                                    <option value="high">สูง</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ปุ่มดำเนินการ -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <button type="button" 
                                onclick="closeEditModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 shadow-sm transition-colors">
                            ยกเลิก
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 rounded-lg shadow-sm transition-colors">
                            บันทึกการแก้ไข
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal ยืนยันการลบงาน -->
<div id="deleteTaskModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop with blur effect -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm"></div>
    
    <!-- Modal Content -->
    <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md">
        <div class="bg-white rounded-xl shadow-2xl">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-red-500 to-red-600">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-white">ยืนยันการลบงาน</h3>
                    <button type="button" onclick="closeDeleteModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 bg-gray-50">
                <div class="flex items-center justify-center text-red-600 mb-4">
                    <i class="bi bi-exclamation-triangle text-5xl"></i>
                </div>
                <p class="text-center text-gray-700 mb-4">คุณแน่ใจหรือไม่ที่ต้องการลบงานนี้?</p>
                <p class="text-center text-gray-500 text-sm mb-6">การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
                
                <div class="flex justify-center space-x-3">
                    <button type="button" 
                            onclick="closeDeleteModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 shadow-sm transition-colors">
                        ยกเลิก
                    </button>
                    <button type="button" 
                            id="confirmDeleteBtn"
                            class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 rounded-lg shadow-sm transition-colors">
                        ยืนยันการลบ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ฟังก์ชันสำหรับเปิดดูรายละเอียดงาน
function viewTask(taskId) {
    fetch(`/task_tracking_system/admin/get_task.php?task_id=${taskId}`)
        .then(response => response.json())
        .then(task => {
            if (task.error) {
                throw new Error(task.error);
            }

            // แสดงข้อมูลในโมดอล
            document.getElementById('viewTaskTitle').textContent = task.title;
            document.getElementById('viewTaskDescription').textContent = task.description || 'ไม่มีรายละเอียด';
            document.getElementById('viewTaskAssignee').textContent = task.assigned_username || 'ไม่ระบุ';
            
            // จัดการสถานะ
            const statusElement = document.getElementById('viewTaskStatus');
            statusElement.textContent = getStatusText(task.status);
            statusElement.className = `inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getStatusClass(task.status)}`;
            
            // จัดการความสำคัญ
            const priorityElement = document.getElementById('viewTaskPriority');
            priorityElement.textContent = getPriorityText(task.priority);
            priorityElement.className = `inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getPriorityClass(task.priority)}`;
            
            // แสดงกำหนดส่ง
            const dueDate = new Date(task.due_date);
            document.getElementById('viewTaskDueDate').textContent = dueDate.toLocaleDateString('th-TH', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            // แสดงโมดอล
            document.getElementById('viewTaskModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('ไม่สามารถดึงข้อมูลงานได้: ' + error.message);
        });
}

// ฟังก์ชันสำหรับปิดโมดอล
function closeViewModal() {
    document.getElementById('viewTaskModal').classList.add('hidden');
}

// Helper functions
function getStatusText(status) {
    const statusMap = {
        'pending': 'รอดำเนินการ',
        'in_progress': 'กำลังดำเนินการ',
        'completed': 'เสร็จสิ้น'
    };
    return statusMap[status] || 'ไม่ระบุ';
}

function getStatusClass(status) {
    const classMap = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800'
    };
    return classMap[status] || 'bg-gray-100 text-gray-800';
}

function getPriorityText(priority) {
    const priorityMap = {
        'low': 'ความสำคัญต่ำ',
        'medium': 'ความสำคัญปานกลาง',
        'high': 'ความสำคัญสูง'
    };
    return priorityMap[priority] || 'ไม่ระบุ';
}

function getPriorityClass(priority) {
    const classMap = {
        'low': 'bg-blue-100 text-blue-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'high': 'bg-red-100 text-red-800'
    };
    return classMap[priority] || 'bg-gray-100 text-gray-800';
}
</script>

<script>
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

<script>
// ฟังก์ชันเปิด Modal แก้ไขงาน
function editTask(taskId) {
    fetch(`/task_tracking_system/admin/get_task.php?task_id=${taskId}`)
        .then(response => response.json())
        .then(task => {
            if (task.error) {
                throw new Error(task.error);
            }

            // กำหนดค่าให้กับฟอร์ม
            document.getElementById('editTaskId').value = task.task_id;
            document.getElementById('editTitle').value = task.title;
            document.getElementById('editDescription').value = task.description;
            document.getElementById('editAssignedTo').value = task.assigned_to || '';
            document.getElementById('editStatus').value = task.status;
            document.getElementById('editPriority').value = task.priority;
            document.getElementById('editDueDate').value = task.due_date;

            // แสดง Modal
            document.getElementById('editTaskModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('ไม่สามารถดึงข้อมูลงานได้: ' + error.message);
        });
}

// ฟังก์ชันปิด Modal แก้ไขงาน
function closeEditModal() {
    document.getElementById('editTaskModal').classList.add('hidden');
}

// ฟังก์ชันอัพเดทข้อมูลงาน
function updateTask(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('editTaskForm'));
    
    fetch('/task_tracking_system/admin/update_task.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        alert('บันทึกการแก้ไขเรียบร้อย');
        closeEditModal();
        location.reload(); // รีโหลดหน้าเพื่อแสดงข้อมูลที่อัพเดท
    })
    .catch(error => {
        console.error('Error:', error);
        alert('ไม่สามารถบันทึกการแก้ไขได้: ' + error.message);
    });
}
</script>

<script>
let taskIdToDelete = null;

// ฟังก์ชันเปิด Modal ยืนยันการลบ
function deleteTask(taskId) {
    taskIdToDelete = taskId;
    document.getElementById('deleteTaskModal').classList.remove('hidden');
    
    // เพิ่ม event listener สำหรับปุ่มยืนยันการลบ
    document.getElementById('confirmDeleteBtn').onclick = function() {
        confirmDelete(taskId);
    };
}

// ฟังก์ชันปิด Modal ยืนยันการลบ
function closeDeleteModal() {
    document.getElementById('deleteTaskModal').classList.add('hidden');
    taskIdToDelete = null;
}

// ฟังก์ชันยืนยันการลบ
function confirmDelete(taskId) {
    fetch(`/task_tracking_system/admin/delete_task.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${taskId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        alert('ลบงานเรียบร้อยแล้ว');
        closeDeleteModal();
        location.reload(); // รีโหลดหน้าเพื่อแสดงข้อมูลที่อัพเดท
    })
    .catch(error => {
        console.error('Error:', error);
        alert('ไม่สามารถลบงานได้: ' + error.message);
    });
}
</script>

<?php include '../footer.php'; ?> 