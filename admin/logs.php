<?php
include '../header.php';    
require_once '../db.php';
require_once '../auth.php';
checkAdmin(); // เฉพาะ admin เท่านั้น

// จำนวนรายการต่อหน้า
$per_page = 20;

// หน้าปัจจุบัน
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// ฟิลเตอร์
$where = "1=1";
$params = [];
$types = "";

if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $where .= " AND l.user_id = ?";
    $params[] = $_GET['user_id'];
    $types .= "i";
}

if (isset($_GET['action']) && !empty($_GET['action'])) {
    $where .= " AND l.action = ?";
    $params[] = $_GET['action'];
    $types .= "s";
}

if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $where .= " AND DATE(l.created_at) >= ?";
    $params[] = $_GET['date_from'];
    $types .= "s";
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $where .= " AND DATE(l.created_at) <= ?";
    $params[] = $_GET['date_to'];
    $types .= "s";
}

// ดึงจำนวนรายการทั้งหมด
$total_sql = "SELECT COUNT(*) as total FROM activity_logs l WHERE $where";
$total_stmt = $conn->prepare($total_sql);
if (!empty($params)) {
    array_pop($params); // ลบ limit ออก
    array_pop($params);
    $total_stmt->bind_param(substr($types, 0, -2), ...$params);
}
$total_stmt->execute();
$total_rows = $total_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

// ดึงข้อมูล logs
$logs_sql = "
    SELECT l.*, u.username 
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.user_id
    WHERE $where
    ORDER BY l.created_at DESC
    LIMIT ? OFFSET ?";

$stmt = $conn->prepare($logs_sql);
if (!empty($params)) {
    $params[] = $per_page;
    $params[] = $offset;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $per_page, $offset);
}

$stmt->execute();
$logs = $stmt->get_result();
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">ประวัติการใช้งานระบบ</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>วันที่-เวลา</th>
                            <th>ผู้ใช้</th>
                            <th>การกระทำ</th>
                            <th>รายละเอียด</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs->num_rows > 0): ?>
                            <?php while ($log = $logs->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                                    <td>
                                        <?php if ($log['user_id']): ?>
                                            <?php echo htmlspecialchars($log['username']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">ไม่ระบุ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php 
                                            echo match($log['action']) {
                                                'LOGIN' => 'bg-success',
                                                'LOGOUT' => 'bg-secondary',
                                                'CREATE' => 'bg-primary',
                                                'UPDATE' => 'bg-warning',
                                                'DELETE' => 'bg-danger',
                                                default => 'bg-info'
                                            };
                                        ?>">
                                            <?php echo htmlspecialchars($log['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['description']); ?></td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($log['ip_address']); ?></small></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">ไม่พบข้อมูล</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 