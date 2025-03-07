<?php
// Include ไฟล์ db.php
require __DIR__ . "/db.php";
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
} else {
    die("Token is missing or invalid.");
}

// ตรวจสอบการรีเซ็ตรหัสผ่าน
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
        $new_password = $_POST['new_password'];
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // SQL query สำหรับการรีเซ็ตรหัสผ่าน
        $sql = "UPDATE users SET password = ? WHERE reset_token = ? AND reset_expires_at > NOW()";
        $stmt = $mysqli->prepare($sql);

        // ตรวจสอบว่า prepare() สำเร็จหรือไม่
        if ($stmt === false) {
            die("SQL prepare failed: " . $mysqli->error);
        }

        // ผูกพารามิเตอร์
        $stmt->bind_param("ss", $new_password_hash, $token);

        // Execute การอัปเดต
        if ($stmt->execute()) {
            // ใช้ header() เพื่อทำการ redirect ไปยังหน้า success
            header("Location: success-page.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        die("New password is required.");
    }
}
?>

<!-- HTML Form สำหรับการรีเซ็ตรหัสผ่าน -->
<form method="post" action="process-reset-password.php?token=<?php echo $token; ?>">
    <label for="new_password">New Password:</label>
    <input type="password" name="new_password" required>
    <button type="submit">Reset Password</button>
</form>
