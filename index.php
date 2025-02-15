<?php

include 'header_index.php'; // รวมส่วนหัวของเว็บไซต์
?>

<!-- Hero Section -->
<section class="bg-primary text-white text-center py-5">
    <div class="container">
        <h1 class="display-4">ยินดีต้อนรับสู่ระบบติดตามงาน</h1>
        <p class="lead">จัดการโปรเจคและงานของคุณได้อย่างมีประสิทธิภาพ</p>
        <a href="login.php" class="btn btn-light btn-lg">เข้าสู่ระบบ</a>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <i class="fas fa-tachometer-alt fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Dashboard</h5>
                        <p class="card-text">ดูภาพรวมของโปรเจคและงานทั้งหมดในระบบ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <i class="fas fa-project-diagram fa-3x mb-3 text-success"></i>
                        <h5 class="card-title">จัดการโปรเจค</h5>
                        <p class="card-text">สร้าง แก้ไข และลบโปรเจคได้อย่างง่ายดาย</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <i class="fas fa-tasks fa-3x mb-3 text-warning"></i>
                        <h5 class="card-title">จัดการงาน</h5>
                        <p class="card-text">มอบหมายงานและติดตามความคืบหน้าได้ทันที</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="bg-light py-5">
    <div class="container text-center">
        <h2 class="mb-4">พร้อมเริ่มต้นใช้งานหรือยัง?</h2>
        <a href="login.php" class="btn btn-primary btn-lg">เข้าสู่ระบบตอนนี้</a>
    </div>
</section>

<?php
include 'footer_index.php'; // รวมส่วนท้ายของเว็บไซต์
?>