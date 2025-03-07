<?php
include 'header.php';
require_once 'db.php';
require_once 'auth.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีการอัปโหลดรูปภาพหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    $user_id = $_SESSION['user_id'];
    
    // ตรวจสอบไฟล์
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
        // สร้างชื่อไฟล์ใหม่
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = $user_id . '_' . time() . '.' . $extension;
        
        // สร้างโฟลเดอร์ถ้ายังไม่มี
        $upload_dir = 'uploads/profiles/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // อัปโหลดไฟล์
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
            // อัปเดตฐานข้อมูล
            $stmt = $mysqli->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_filename, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['profile_image'] = $new_filename;
                $_SESSION['success'] = "อัปเดตรูปโปรไฟล์สำเร็จ";
            } else {
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตฐานข้อมูล";
            }
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
        }
    } else {
        $_SESSION['error'] = "กรุณาอัปโหลดไฟล์รูปภาพขนาดไม่เกิน 5MB";
    }
    
    header("Location: profile.php");
    exit();
}

// ดึงข้อมูลผู้ใช้
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// ดึงสถิติงานของผู้ใช้
$tasks_stmt = $mysqli->prepare("
    SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as ongoing_tasks
    FROM tasks 
    WHERE assigned_to = ? OR created_by = ?
");
$tasks_stmt->bind_param("ii", $user_id, $user_id);
$tasks_stmt->execute();
$tasks_stats = $tasks_stmt->get_result()->fetch_assoc();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- โปรไฟล์การ์ด -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- หัวข้อโปรไฟล์ -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-4 py-8">
                <div class="flex justify-center">
                    <div class="relative group">
                        <img class="h-32 w-32 rounded-full object-cover border-4 border-white shadow-lg" 
                             src="uploads/profiles/<?php echo $user['profile_image'] ?? 'default-profile.png'; ?>" 
                             alt="Profile">
                        <label for="profile_image" class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-lg cursor-pointer hover:bg-gray-100">
                            <i class="bi bi-camera-fill text-gray-600"></i>
                        </label>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p class="text-indigo-100"><?php echo ucfirst($user['role']); ?></p>
                </div>
            </div>

            <!-- สถิติผู้ใช้ -->
            <div class="grid grid-cols-3 gap-4 p-4 bg-gray-50">
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600"><?php echo $tasks_stats['total_tasks']; ?></div>
                    <div class="text-sm text-gray-500">งานทั้งหมด</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600"><?php echo $tasks_stats['completed_tasks']; ?></div>
                    <div class="text-sm text-gray-500">งานที่เสร็จแล้ว</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600"><?php echo $tasks_stats['ongoing_tasks']; ?></div>
                    <div class="text-sm text-gray-500">งานที่กำลังทำ</div>
                </div>
            </div>

            <!-- ข้อมูลผู้ใช้และฟอร์มแก้ไข -->
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">ข้อมูลส่วนตัว</h3>
                <form action="profile.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- ซ่อน input file ไว้ -->
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" class="hidden" 
                           onchange="this.form.submit()">

                    <!-- ข้อมูลอื่นๆ -->
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">อีเมล</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                   disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">วันที่สมัคร</label>
                            <input type="text" value="<?php echo date('d/m/Y', strtotime($user['created_at'])); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" 
                                   disabled>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- กล่องแสดงคำแนะนำ -->
        <div class="mt-6 bg-blue-50 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="bi bi-info-circle-fill text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">คำแนะนำ</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>คลิกที่ไอคอนกล้องเพื่อเปลี่ยนรูปโปรไฟล์ของคุณ</p>
                        <p>รองรับไฟล์ภาพ PNG, JPG และ GIF ขนาดไม่เกิน 5MB</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// เพิ่ม preview รูปภาพก่อนอัปโหลด
document.getElementById('profile_image').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('img.rounded-full').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>

<?php include 'footer.php'; ?> 