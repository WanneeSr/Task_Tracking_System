<?php
require_once 'auth.php';
require_once 'db.php';
checkLogin();

// กำหนดตัวแปร $current_page สำหรับ active menu
$current_page = basename($_SERVER['PHP_SELF']);

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// ดึงข้อมูลแผนก
$departments = $conn->query("SELECT * FROM departments ORDER BY name");

// จัดการการอัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $department_id = !empty($_POST['department_id']) ? $_POST['department_id'] : null;
    $phone = trim($_POST['phone']);
    $success = true;
    $message = '';

    // ตรวจสอบว่ามีการอัพโหลดรูปภาพหรือไม่
    if (!empty($_FILES['profile_image']['name'])) {
        $file = $_FILES['profile_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $success = false;
            $message = 'รองรับเฉพาะไฟล์ภาพ JPG, PNG และ GIF เท่านั้น';
        } elseif ($file['size'] > $max_size) {
            $success = false;
            $message = 'ขนาดไฟล์ต้องไม่เกิน 5MB';
        } else {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
            $upload_path = 'uploads/profiles/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // ลบรูปเก่า (ถ้ามี)
                if (!empty($user['profile_image']) && $user['profile_image'] != 'default-profile.png') {
                    @unlink('uploads/profiles/' . $user['profile_image']);
                }
                
                // อัปเดตชื่อไฟล์ในฐานข้อมูล
                $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
                $stmt->bind_param("si", $filename, $user_id);
                if ($stmt->execute()) {
                    $_SESSION['profile_image'] = $filename;
                    // เพิ่ม timestamp เพื่อป้องกันการ cache
                    $_SESSION['profile_updated'] = time();
                }
            } else {
                $success = false;
                $message = 'เกิดข้อผิดพลาดในการอัพโหลดไฟล์';
            }
        }
    }

    if ($success) {
        // อัปเดตข้อมูลผู้ใช้
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, department_id = ?, phone = ? WHERE user_id = ?");
        $stmt->bind_param("ssisi", $username, $email, $department_id, $phone, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $_SESSION['success'] = 'อัปเดตข้อมูลสำเร็จ';
            echo "<script>window.location.href = 'edit_profile.php';</script>";
            exit();
        } else {
            $_SESSION['error'] = 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล';
        }
    } else {
        $_SESSION['error'] = $message;
    }
}

include 'header.php';
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">แก้ไขข้อมูลส่วนตัว</h1>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Profile Image -->
                <div class="flex items-center space-x-6">
                    <div class="shrink-0">
                        <img class="h-32 w-32 object-cover rounded-full border-4 border-gray-200" 
                             src="/task_tracking_system/uploads/profiles/<?php echo $user['profile_image'] ?? 'default-profile.png'; ?>" 
                             alt="Profile">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">รูปโปรไฟล์</label>
                        <div class="mt-1 flex items-center">
                            <input type="file" name="profile_image" accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">PNG, JPG หรือ GIF ขนาดไม่เกิน 5MB</p>
                    </div>
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">ชื่อผู้ใช้</label>
                    <input type="text" name="username" id="username" required
                           value="<?php echo htmlspecialchars($user['username']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">อีเมล</label>
                    <input type="email" name="email" id="email" required
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700">แผนก</label>
                    <select name="department_id" id="department_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- เลือกแผนก --</option>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo $dept['department_id']; ?>"
                                    <?php echo $dept['department_id'] == $user['department_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">เบอร์โทรศัพท์</label>
                    <input type="tel" name="phone" id="phone"
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4">
                    <a href="javascript:history.back()" 
                       class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        ย้อนกลับ
                    </a>
                    <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Preview image before upload
document.querySelector('input[type="file"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('img').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'footer.php'; ?> 