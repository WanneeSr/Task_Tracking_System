<?php
include '../header.php';    
require_once '../db.php';
require_once '../auth.php';
checkAdmin(); // เฉพาะ admin เท่านั้น

// จัดการการลบผู้ใช้
if (isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    
    // ตรวจสอบว่าไม่ใช่การลบตัวเอง
    if ($user_id == $_SESSION['user_id']) {
        header("Location: users.php?error=cannot_delete_self");
        exit();
    }
    
    // เริ่ม transaction
    $conn->begin_transaction();
    
    try {
        // 1. ลบข้อมูลจาก activity_logs
        $conn->query("DELETE FROM activity_logs WHERE user_id = $user_id");
        
        // 2. ลบข้อมูลจาก tasks
        $conn->query("DELETE FROM tasks WHERE created_by = $user_id");
        
        // 3. สุดท้ายค่อยลบข้อมูลจากตาราง users
        $result = $conn->query("DELETE FROM users WHERE user_id = $user_id");
        
        if ($result) {
            $conn->commit();
            // บันทึก activity log สำหรับการลบผู้ใช้
            $admin_id = $_SESSION['user_id'];
            $log_description = "ลบผู้ใช้ ID: $user_id ออกจากระบบ";
            $conn->query("INSERT INTO activity_logs (user_id, action, description) VALUES ($admin_id, 'delete_user', '$log_description')");
            
            header("Location: users.php?success=user_deleted");
            exit();
        } else {
            throw new Exception("Failed to delete user");
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error deleting user: " . $e->getMessage());
        header("Location: users.php?error=delete_failed&message=" . urlencode($e->getMessage()));
        exit();
    }
}

// ดึงข้อมูลผู้ใช้ทั้งหมด
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

// จัดการข้อความแจ้งเตือน
$message = '';
$message_type = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'cannot_delete_self':
            $message = 'ไม่สามารถลบบัญชีของตัวเองได้';
            break;
        case 'delete_failed':
            $message = 'เกิดข้อผิดพลาดในการลบผู้ใช้';
            if (isset($_GET['message'])) {
                $message .= ': ' . htmlspecialchars($_GET['message']);
            }
            break;
        default:
            $message = 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ';
    }
    $message_type = 'error';
}

if (isset($_GET['success'])) {
    $message = 'ลบผู้ใช้สำเร็จ';
    $message_type = 'success';
}
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- หัวข้อหน้า -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">จัดการผู้ใช้</h1>
                <p class="mt-1 text-sm text-gray-500">จัดการข้อมูลผู้ใช้ทั้งหมดในระบบ</p>
            </div>
            <button onclick="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg 
                           text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 
                           focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                เพิ่มผู้ใช้ใหม่
            </button>
        </div>

        <!-- แสดงข้อความแจ้งเตือน -->
        <?php if ($message): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-md <?php echo $message_type === 'success' ? 'bg-green-50' : 'bg-red-50'; ?> p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <?php if ($message_type === 'success'): ?>
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        <?php else: ?>
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium <?php echo $message_type === 'success' ? 'text-green-800' : 'text-red-800'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ตารางแสดงข้อมูล -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ชื่อผู้ใช้
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                อีเมล
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                บทบาท
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                วันที่สร้าง
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                จัดการ
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-700 font-medium text-lg">
                                                <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($row['username']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($row['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php 
                                    echo match($row['role']) {
                                        'admin' => 'bg-red-100 text-red-800',
                                        'manager' => 'bg-blue-100 text-blue-800',
                                        'employee' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    }; 
                                    ?>">
                                    <?php echo htmlspecialchars($row['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="javascript:void(0)" 
                                       onclick="openEditModal(<?php echo $row['user_id']; ?>)"
                                       class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 
                                              px-3 py-1 rounded-md transition-colors duration-200">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" class="inline-block" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?');">
                                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                        <button type="submit" name="delete_user" 
                                                class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 
                                                       px-3 py-1 rounded-md transition-colors duration-200">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- เพิ่ม Modal สำหรับแก้ไขข้อมูล -->
<div id="editUserModal" class="fixed inset-0 z-50 hidden">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

    <!-- Modal Content -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">แก้ไขข้อมูลผู้ใช้</h3>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <form id="editUserForm" class="space-y-4">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <div>
                        <label for="edit_username" class="block text-sm font-medium text-gray-700">ชื่อผู้ใช้</label>
                        <input type="text" id="edit_username" name="username" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="edit_email" class="block text-sm font-medium text-gray-700">อีเมล</label>
                        <input type="email" id="edit_email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="edit_role" class="block text-sm font-medium text-gray-700">บทบาท</label>
                        <select id="edit_role" name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="admin">ผู้ดูแลระบบ</option>
                            <option value="manager">ผู้จัดการ</option>
                            <option value="employee">พนักงาน</option>
                        </select>
                    </div>

                    <div>
                        <label for="edit_password" class="block text-sm font-medium text-gray-700">รหัสผ่านใหม่ (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</label>
                        <input type="password" id="edit_password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-2 rounded-b-lg">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    ยกเลิก
                </button>
                <button type="button" onclick="saveChanges()" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                    บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal สำหรับเพิ่มผู้ใช้ใหม่ -->
<div id="addUserModal" class="fixed inset-0 z-50 hidden">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <!-- Modal Content -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">เพิ่มผู้ใช้ใหม่</h3>
                    <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4">
                <form id="addUserForm" class="space-y-4" onsubmit="event.preventDefault(); addUser();">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">ชื่อผู้ใช้ *</label>
                        <input type="text" id="username" name="username" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">อีเมล *</label>
                        <input type="email" id="email" name="email" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน *</label>
                        <input type="password" id="password" name="password" required
                               minlength="6"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                                      focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</p>
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">บทบาท *</label>
                        <select id="role" name="role" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">เลือกบทบาท</option>
                            <option value="admin">ผู้ดูแลระบบ</option>
                            <option value="manager">ผู้จัดการ</option>
                            <option value="employee">พนักงาน</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-2 rounded-b-lg">
                <button type="button" onclick="closeAddModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 
                               rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 
                               focus:ring-offset-2 focus:ring-indigo-500">
                    ยกเลิก
                </button>
                <button type="button" onclick="addUser()"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md 
                               hover:bg-indigo-700 focus:outline-none focus:ring-2 
                               focus:ring-offset-2 focus:ring-indigo-500">
                    เพิ่มผู้ใช้
                </button>
            </div>
        </div>
    </div>
</div>

<!-- เพิ่ม JavaScript -->
<script>
// ฟังก์ชันเปิด Modal และดึงข้อมูลผู้ใช้
function openEditModal(userId) {
    // เปิด Modal
    const modal = document.getElementById('editUserModal');
    modal.classList.remove('hidden');

    // ดึงข้อมูลผู้ใช้
    fetch('get_user.php?id=' + userId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // กรอกข้อมูลในฟอร์ม
                document.getElementById('edit_user_id').value = data.data.user_id;
                document.getElementById('edit_username').value = data.data.username;
                document.getElementById('edit_email').value = data.data.email;
                document.getElementById('edit_role').value = data.data.role;
            } else {
                alert('ไม่สามารถดึงข้อมูลผู้ใช้ได้: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการดึงข้อมูล');
        });
}

// ฟังก์ชันปิด Modal
function closeEditModal() {
    const modal = document.getElementById('editUserModal');
    modal.classList.add('hidden');
    document.getElementById('editUserForm').reset();
}

// ฟังก์ชันบันทึกการเปลี่ยนแปลง
function saveChanges() {
    const form = document.getElementById('editUserForm');
    const formData = new FormData(form);

    fetch('update_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('บันทึกข้อมูลสำเร็จ');
            closeEditModal();
            window.location.reload(); // รีโหลดหน้าเพื่อแสดงข้อมูลที่อัปเดต
        } else {
            alert('เกิดข้อผิดพลาด: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
    });
}

// เพิ่ม Event Listener สำหรับปิด Modal ด้วยปุ่ม ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditModal();
    }
});

// ปิด Modal เมื่อคลิกที่ overlay
document.querySelector('#editUserModal > div:first-child').addEventListener('click', function(event) {
    if (event.target === this) {
        closeEditModal();
    }
});

function addUser() {
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);

    // แสดงสถานะกำลังทำงาน
    const submitBtn = document.querySelector('button[onclick="addUser()"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'กำลังเพิ่มผู้ใช้...';

    fetch('add_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // ปิด Modal และรีเซ็ตฟอร์ม
            closeAddModal();
            form.reset();
            
            // แสดงข้อความสำเร็จ
            const successMessage = document.createElement('div');
            successMessage.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
            successMessage.innerHTML = `
                <strong class="font-bold">สำเร็จ!</strong>
                <span class="block sm:inline"> ${data.message}</span>
            `;
            document.body.appendChild(successMessage);

            // ซ่อนข้อความหลังจาก 3 วินาที
            setTimeout(() => {
                successMessage.remove();
                // รีโหลดหน้าหลังจากแสดงข้อความ
                window.location.reload();
            }, 2000);
        } else {
            // แสดงข้อความผิดพลาด
            const errorMessage = document.createElement('div');
            errorMessage.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50';
            errorMessage.innerHTML = `
                <strong class="font-bold">ผิดพลาด!</strong>
                <span class="block sm:inline"> ${data.message}</span>
            `;
            document.body.appendChild(errorMessage);

            // ซ่อนข้อความผิดพลาดหลังจาก 3 วินาที
            setTimeout(() => {
                errorMessage.remove();
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // แสดงข้อความผิดพลาดจากระบบ
        const errorMessage = document.createElement('div');
        errorMessage.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50';
        errorMessage.innerHTML = `
            <strong class="font-bold">ผิดพลาด!</strong>
            <span class="block sm:inline"> เกิดข้อผิดพลาดในการเพิ่มผู้ใช้</span>
        `;
        document.body.appendChild(errorMessage);

        // ซ่อนข้อความผิดพลาดหลังจาก 3 วินาที
        setTimeout(() => {
            errorMessage.remove();
        }, 3000);
    })
    .finally(() => {
        // คืนค่าปุ่มกลับสู่สถานะปกติ
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function openAddModal() {
    const modal = document.getElementById('addUserModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeAddModal() {
    const modal = document.getElementById('addUserModal');
    if (modal) {
        modal.classList.add('hidden');
        document.getElementById('addUserForm').reset();
    }
}

// เพิ่ม Event Listener สำหรับการกด Enter ในฟอร์ม
document.getElementById('addUserForm').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        addUser();
    }
});

// เพิ่ม Event Listener สำหรับการกด ESC เพื่อปิด Modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
    }
});
</script>

<!-- เพิ่ม CSS สำหรับ animation -->
<style>
@keyframes slideIn {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(-100%);
        opacity: 0;
    }
}

.fixed {
    animation: slideIn 0.5s ease-out;
}

.fixed.hiding {
    animation: slideOut 0.5s ease-in;
}
</style>

<?php include '../footer.php'; ?> 