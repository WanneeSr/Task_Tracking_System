<?php
// ฟังก์ชันสำหรับบันทึก log
function logActivity($user_id, $action, $description) {
    global $mysqli;
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("issss", $user_id, $action, $description, $ip, $user_agent);
    $stmt->execute();
}
?>