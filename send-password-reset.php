<?php
$email = $_POST["email"];

$token = bin2hex(random_bytes(16));

$token_hash = hash("sha256", $token);

$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

$mysqli = require __DIR__ . "/db.php";

// SQL สำหรับการอัพเดต reset_token
$sql = "UPDATE users
        SET reset_token = ?,
            reset_expires_at = ?
        WHERE email = ?";

// เตรียมคำสั่ง SQL
$stmt = $mysqli->prepare($sql);

// ตรวจสอบว่าเตรียมคำสั่งได้สำเร็จหรือไม่
if ($stmt === false) {
    // หากเตรียมคำสั่งไม่สำเร็จ ให้แสดงข้อผิดพลาด
    die('Error preparing statement: ' . $mysqli->error);
}

// ผูกพารามิเตอร์
$stmt->bind_param("sss", $token_hash, $expiry, $email);

// Execute the query
$stmt->execute();

// ตรวจสอบว่าอัพเดตฐานข้อมูลสำเร็จหรือไม่
if ($stmt->affected_rows > 0) {

    $mail = require __DIR__ . "/mailer.php";

    $mail->setFrom("noreply@example.com");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset";
    $mail->Body = <<<END

    Click <a href="http://localhost/Task_tracking_system/process-reset-password.php?token=$token">here</a> 
    to reset your password.

    END;

    try {
        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
    }
}

echo "Message sent, please check your inbox.";
?>


<!-- echo "Message sent, please check your inbox."; -->