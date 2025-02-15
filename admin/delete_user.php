<?php
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// ป้องกันการลบตัวเอง
if ($user_id == $_SESSION['user_id']) {
    header("Location: users.php?error=cannot_delete_self");
    exit();
}

// ลบผู้ใช้
$sql = "DELETE FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    header("Location: users.php?success=deleted");
} else {
    header("Location: users.php?error=delete_failed");
}
exit();
?> 