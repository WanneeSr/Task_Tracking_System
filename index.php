<?php
include 'header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-indigo-600 to-blue-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-white sm:text-5xl md:text-6xl">
                ระบบจัดการงานที่ทันสมัย
            </h1>
            <p class="mt-3 max-w-md mx-auto text-base text-indigo-100 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                จัดการงานของคุณอย่างมีประสิทธิภาพ ติดตามความคืบหน้า และทำงานร่วมกันได้ดียิ่งขึ้น
            </p>
            <div class="mt-10 flex justify-center gap-4">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50">
                        เข้าสู่ระบบ
                    </a>
                    <a href="register.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-800 hover:bg-indigo-700">
                        เริ่มต้นใช้งานฟรี
                    </a>
                <?php else: ?>
                    <a href="dashboard.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-800 hover:bg-indigo-700">
                        ไปที่แดชบอร์ด
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900">
                คุณสมบัติเด่น
            </h2>
            <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">
                ทุกฟีเจอร์ที่คุณต้องการในการจัดการงาน
            </p>
        </div>

        <div class="mt-16 grid grid-cols-1 gap-8 md:grid-cols-3">
            <!-- Feature 1: Kanban -->
            <a href="/task_tracking_system/kanban.php" class="group">
                <div class="relative p-6 bg-white rounded-lg shadow-sm group-hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 group-hover:bg-indigo-200 flex items-center justify-center mb-4 transition-colors">
                        <i class="bi bi-kanban text-2xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">จัดการงานแบบ Kanban</h3>
                    <p class="mt-2 text-base text-gray-500">
                        จัดการงานด้วยระบบ Kanban ที่ใช้งานง่าย ติดตามความคืบหน้าได้ทันที
                    </p>
                </div>
            </a>

            <!-- Feature 2: Team Collaboration -->
            <a href="/task_tracking_system/team.php" class="group">
                <div class="relative p-6 bg-white rounded-lg shadow-sm group-hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 group-hover:bg-indigo-200 flex items-center justify-center mb-4 transition-colors">
                        <i class="bi bi-people text-2xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">ทำงานร่วมกันเป็นทีม</h3>
                    <p class="mt-2 text-base text-gray-500">
                        แชร์งานและทำงานร่วมกันกับทีมได้อย่างมีประสิทธิภาพ
                    </p>
                </div>
            </a>

            <!-- Feature 3: Reports -->
            <a href="/task_tracking_system/reports.php" class="group">
                <div class="relative p-6 bg-white rounded-lg shadow-sm group-hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 group-hover:bg-indigo-200 flex items-center justify-center mb-4 transition-colors">
                        <i class="bi bi-graph-up text-2xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">รายงานและการวิเคราะห์</h3>
                    <p class="mt-2 text-base text-gray-500">
                        ดูรายงานและวิเคราะห์ประสิทธิภาพการทำงานได้แบบเรียลไทม์
                    </p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-3 text-center">
            <div>
                <div class="text-4xl font-extrabold text-indigo-600">500+</div>
                <div class="mt-2 text-lg text-gray-600">ผู้ใช้งาน</div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-indigo-600">10,000+</div>
                <div class="mt-2 text-lg text-gray-600">งานที่สำเร็จ</div>
            </div>
            <div>
                <div class="text-4xl font-extrabold text-indigo-600">98%</div>
                <div class="mt-2 text-lg text-gray-600">ความพึงพอใจ</div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="bg-indigo-700">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                พร้อมเริ่มต้นใช้งานหรือยัง?
            </h2>
            <p class="mt-4 text-lg text-indigo-100">
                เริ่มต้นใช้งานได้ฟรีวันนี้ ไม่มีค่าใช้จ่ายซ่อนเร้น
            </p>
            <div class="mt-8">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50">
                        สมัครใช้งานฟรี
                    </a>
                <?php else: ?>
                    <a href="dashboard.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50">
                        ไปที่แดชบอร์ด
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>