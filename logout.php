<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'LOGOUT', 'ออกจากระบบ');
}

// ลบ session
session_destroy();
header("Location: login.php");
exit();
?> 