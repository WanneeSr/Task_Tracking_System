<?php
include '../header.php';
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $task_id = $_POST['task_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];

    // ตรวจสอบข้อมูล
    $errors = [];
    if (empty($title)) {
        $errors[] = "กรุณาระบุหัวข้องาน";
    }
    if (empty($due_date)) {
        $errors[] = "กรุณาระบุวันกำหนดส่ง";
    }
    if (!in_array($status, ['pending', 'in_progress', 'completed'])) {
        $errors[] = "สถานะไม่ถูกต้อง";
    }
    if (!in_array($priority, ['low', 'medium', 'high'])) {
        $errors[] = "ระดับความสำคัญไม่ถูกต้อง";
    }

    // ถ้าไม่มี error ให้อัพเดทข้อมูล
    if (empty($errors)) {
        if ($assigned_to === null) {
            // กรณีไม่ได้เลือกผู้รับผิดชอบ
            $sql = "UPDATE tasks 
                    SET title = ?, 
                        description = ?, 
                        assigned_to = NULL, 
                        status = ?, 
                        priority = ?, 
                        due_date = ?, 
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE task_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", 
                $title, 
                $description, 
                $status, 
                $priority, 
                $due_date, 
                $task_id
            );
        } else {
            // กรณีเลือกผู้รับผิดชอบ
            $sql = "UPDATE tasks 
                    SET title = ?, 
                        description = ?, 
                        assigned_to = ?, 
                        status = ?, 
                        priority = ?, 
                        due_date = ?, 
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE task_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisssi", 
                $title, 
                $description, 
                $assigned_to, 
                $status, 
                $priority, 
                $due_date, 
                $task_id
            );
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "อัพเดทข้อมูลงานเรียบร้อยแล้ว";
            header("Location: tasks.php");
            exit();
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัพเดทข้อมูล: " . $stmt->error;
        }
    }

    // ถ้ามี error
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: edit_task.php?id=" . $task_id);
        exit();
    }
} else {
    // ถ้าไม่ใช่ POST request
    header("Location: tasks.php");
    exit();
}
?> 