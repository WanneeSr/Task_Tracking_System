<?php
session_start();

// ตรวจสอบว่ามี query parameter 'reset' หรือไม่ เพื่อแสดงข้อความสำเร็จ
$resetSuccess = isset($_GET['reset']) && $_GET['reset'] == 1;
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สำเร็จ | รีเซ็ตรหัสผ่าน</title>
    <!-- ใช้ Bootstrap CSS จาก CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KyZXEJx3HQ5p7gxaZsE6Xv5sdV6LwL2nF6f6HpJ6Dx3uM0yy8F5+z87OM6c0kzzy" crossorigin="anonymous">
    <style>
        /* ปรับสไตล์พื้นหลัง */
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 600px;
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if ($resetSuccess): ?>
        <div class="alert alert-success text-center" role="alert">
            <h4 class="alert-heading">สำเร็จ!</h4>
            <p>รหัสผ่านของคุณได้ถูกรีเซ็ตเรียบร้อยแล้ว</p>
            <hr>
            <p class="mb-0">คุณสามารถเข้าสู่ระบบใหม่ได้โดยใช้รหัสผ่านที่รีเซ็ตแล้ว</p>
        </div>
        <?php else: ?>
        <div class="alert alert-danger text-center" role="alert">
            <h4 class="alert-heading">เกิดข้อผิดพลาด</h4>
            <p>ไม่สามารถรีเซ็ตรหัสผ่านได้ กรุณาลองใหม่อีกครั้ง</p>
        </div>
        <?php endif; ?>
        
        <!-- ปุ่มกลับไปที่หน้า Login -->
        <div class="text-center">
            <a href="login.php" class="btn btn-primary">กลับไปที่หน้าล็อกอิน</a>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js และ jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
        integrity="sha384-oBqDVmMz4fnFO9gyb2hDphvYg5KNTXsvg1SK8fn4VjsnXtWv6jjXZyA+bxC1dG1v" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"
        integrity="sha384-pzjw8f+ua7Kw1TIq0cFfM47p0xkFnn6erC5FvIbs6PH44o6zV1xk8hO7uR2hPp4i"
        crossorigin="anonymous"></script>
</body>

</html>
