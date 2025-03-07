<?php
require_once '../db.php';

$sql = "CREATE TABLE IF NOT EXISTS departments (
    department_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($mysqli->query($sql) === TRUE) {
    echo "สร้างตาราง departments สำเร็จ";
    
    // เพิ่มข้อมูลตัวอย่าง
    $sample_departments = [
        ['name' => 'ฝ่ายพัฒนาซอฟต์แวร์', 'description' => 'พัฒนาและดูแลระบบซอฟต์แวร์'],
        ['name' => 'ฝ่ายการตลาด', 'description' => 'วางแผนและดำเนินการด้านการตลาด'],
        ['name' => 'ฝ่ายบัญชี', 'description' => 'จัดการด้านการเงินและบัญชี'],
        ['name' => 'ฝ่ายทรัพยากรบุคคล', 'description' => 'บริหารจัดการบุคลากร']
    ];

    $insert_sql = "INSERT INTO departments (name, description) VALUES (?, ?)";
    $stmt = $mysqli->prepare($insert_sql);

    foreach ($sample_departments as $dept) {
        $stmt->bind_param("ss", $dept['name'], $dept['description']);
        $stmt->execute();
    }

    echo "<br>เพิ่มข้อมูลตัวอย่างสำเร็จ";
} else {
    echo "เกิดข้อผิดพลาดในการสร้างตาราง: " . $mysqli->error;
}

// เพิ่มคอลัมน์ department_id ในตาราง users ถ้ายังไม่มี
$check_column = "SHOW COLUMNS FROM users LIKE 'department_id'";
$result = $mysqli->query($check_column);

if ($result->num_rows == 0) {
    $alter_sql = "ALTER TABLE users ADD department_id INT,
                  ADD FOREIGN KEY (department_id) REFERENCES departments(department_id)";
    
    if ($mysqli->query($alter_sql) === TRUE) {
        echo "<br>เพิ่มคอลัมน์ department_id ในตาราง users สำเร็จ";
    } else {
        echo "<br>เกิดข้อผิดพลาดในการเพิ่มคอลัมน์: " . $mysqli->error;
    }
}

$mysqli->close();
?> 