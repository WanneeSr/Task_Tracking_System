<?php
// header.php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการงาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/task_tracking_system">ระบบจัดการงาน</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <?php if ($_SESSION['role'] === 'employee'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" 
                                   href="/task_tracking_system/employee/dashboard.php">
                                    <i class="bi bi-speedometer2"></i> แดชบอร์ด
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page === 'tasks.php' ? 'active' : ''; ?>" 
                                   href="/task_tracking_system/employee/tasks.php">
                                    <i class="bi bi-list-task"></i> งานของฉัน
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'manager'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" 
                                   href="/task_tracking_system/manager/dashboard.php">
                                    <i class="bi bi-speedometer2"></i> แดชบอร์ด
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page === 'tasks.php' ? 'active' : ''; ?>" 
                                   href="/task_tracking_system/manager/tasks.php">
                                    <i class="bi bi-list-task"></i> จัดการงาน
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" 
                                   href="/task_tracking_system/admin/dashboard.php">
                                    <i class="bi bi-speedometer2"></i> แดชบอร์ด
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" 
                                   href="/task_tracking_system/admin/users.php">
                                    <i class="bi bi-people"></i> จัดการผู้ใช้
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page === 'tasks.php' ? 'active' : ''; ?>" 
                                   href="/task_tracking_system/admin/tasks.php">
                                    <i class="bi bi-list-check"></i> ภาพรวมงาน
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page === 'logs.php' ? 'active' : ''; ?>" 
                                   href="/task_tracking_system/admin/logs.php">
                                    <i class="bi bi-clock-history"></i> ประวัติการใช้งาน
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" 
                               data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> 
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="/task_tracking_system/<?php echo $_SESSION['role']; ?>/profile.php">
                                        <i class="bi bi-person"></i> โปรไฟล์
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="/task_tracking_system/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>