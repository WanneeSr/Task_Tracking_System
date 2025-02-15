<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $created_by = $_SESSION['user_id'];

    // ตรวจสอบข้อมูล
    $errors = [];
    if (empty($title)) {
        $errors[] = "กรุณาระบุหัวข้องาน";
    }
    if (empty($due_date)) {
        $errors[] = "กรุณาระบุวันกำหนดส่ง";
    }
    if (!in_array($priority, ['low', 'medium', 'high'])) {
        $errors[] = "ระดับความสำคัญไม่ถูกต้อง";
    }

    // ถ้าไม่มี error ให้บันทึกข้อมูล
    if (empty($errors)) {
        $sql = "INSERT INTO tasks (title, description, assigned_to, priority, due_date, created_by, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssissi", $title, $description, $assigned_to, $priority, $due_date, $created_by);
            
            if ($stmt->execute()) {
                // บันทึกสำเร็จ
                $_SESSION['success'] = "เพิ่มงานใหม่เรียบร้อยแล้ว";
                header("Location: tasks.php");
                exit();
            } else {
                $errors[] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt->error;
            }
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $conn->error;
        }
    }

    // ถ้ามี error ให้กลับไปที่หน้า tasks พร้อมแสดงข้อความ error
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: tasks.php");
        exit();
    }
} else {
    // ถ้าไม่ใช่ POST request ให้กลับไปที่หน้า tasks
    header("Location: tasks.php");
    exit();
}
?> 