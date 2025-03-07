<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project_management";

// สร้างการเชื่อมต่อ
$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

return $mysqli;

// ตั้งค่า charset เป็น utf8
$mysqli->set_charset("utf8");

function logActivity($user_id, $action, $description) {
    global $mysqli;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
    return $stmt->execute();
}
?>
