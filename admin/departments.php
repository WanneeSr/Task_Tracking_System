<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// จัดการการเพิ่มแผนก
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        $stmt = $mysqli->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
    }
    // จัดการการลบแผนก
    elseif ($_POST['action'] === 'delete' && isset($_POST['department_id'])) {
        $stmt = $mysqli->prepare("DELETE FROM departments WHERE department_id = ?");
        $stmt->bind_param("i", $_POST['department_id']);
        $stmt->execute();
    }
    // จัดการการแก้ไขแผนก
    elseif ($_POST['action'] === 'edit') {
        $id = $_POST['department_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        $stmt = $mysqli->prepare("UPDATE departments SET name = ?, description = ? WHERE department_id = ?");
        $stmt->bind_param("ssi", $name, $description, $id);
        $stmt->execute();
    }
}

// ดึงข้อมูลแผนกทั้งหมด
$departments = $mysqli->query("SELECT * FROM departments ORDER BY name");
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">จัดการแผนก</h1>
                    <p class="mt-2 text-sm text-gray-600">จัดการข้อมูลแผนกต่างๆ ในระบบ</p>
                </div>
                <button onclick="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    เพิ่มแผนกใหม่
                </button>
            </div>
        </div>

        <!-- Departments List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                <?php if ($departments->num_rows > 0): ?>
                    <?php while($dept = $departments->fetch_assoc()): ?>
                        <li>
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-medium text-gray-900 truncate">
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            <?php echo htmlspecialchars($dept['description']); ?>
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($dept)); ?>)"
                                                class="inline-flex items-center px-3 py-1 border border-transparent rounded-md text-sm font-medium text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            แก้ไข
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $dept['department_id']; ?>, '<?php echo htmlspecialchars($dept['name']); ?>')"
                                                class="inline-flex items-center px-3 py-1 border border-transparent rounded-md text-sm font-medium text-red-600 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            ลบ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="px-4 py-4 sm:px-6 text-center text-gray-500">
                        ไม่พบข้อมูลแผนก
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4">เพิ่มแผนกใหม่</h3>
            <form id="addForm" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">ชื่อแผนก</label>
                        <input type="text" name="name" id="name" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">รายละเอียด</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                <div class="mt-5 flex justify-end space-x-3">
                    <button type="button" onclick="closeAddModal()"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        ยกเลิก
                    </button>
                    <button type="submit"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4">แก้ไขแผนก</h3>
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="department_id" id="edit_department_id">
                <div class="space-y-4">
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700">ชื่อแผนก</label>
                        <input type="text" name="name" id="edit_name" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="edit_description" class="block text-sm font-medium text-gray-700">รายละเอียด</label>
                        <textarea name="description" id="edit_description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                <div class="mt-5 flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        ยกเลิก
                    </button>
                    <button type="submit"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.getElementById('addForm').reset();
}

function openEditModal(department) {
    document.getElementById('edit_department_id').value = department.department_id;
    document.getElementById('edit_name').value = department.name;
    document.getElementById('edit_description').value = department.description;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editForm').reset();
}

function confirmDelete(id, name) {
    if (confirm(`ต้องการลบแผนก "${name}" ใช่หรือไม่?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="department_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// ปิด Modal เมื่อคลิกพื้นหลัง
window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    if (event.target === addModal) {
        closeAddModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
}
</script>

<?php include '../footer.php'; ?> 