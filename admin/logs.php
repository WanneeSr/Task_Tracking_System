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
$total_stmt = $mysqli->prepare($total_sql);
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

$stmt = $mysqli->prepare($logs_sql);
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

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">ประวัติการใช้งานระบบ</h1>
            <p class="mt-2 text-sm text-gray-600">บันทึกกิจกรรมทั้งหมดในระบบ</p>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <form class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">ผู้ใช้</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">ทั้งหมด</option>
                        <?php
                        $users = $mysqli->query("SELECT user_id, username FROM users ORDER BY username");
                        while ($user = $users->fetch_assoc()) {
                            $selected = isset($_GET['user_id']) && $_GET['user_id'] == $user['user_id'] ? 'selected' : '';
                            echo "<option value='{$user['user_id']}' {$selected}>{$user['username']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="action" class="block text-sm font-medium text-gray-700 mb-1">การกระทำ</label>
                    <select name="action" id="action" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">ทั้งหมด</option>
                        <?php
                        $actions = ['LOGIN', 'LOGOUT', 'CREATE', 'UPDATE', 'DELETE'];
                        foreach ($actions as $action) {
                            $selected = isset($_GET['action']) && $_GET['action'] == $action ? 'selected' : '';
                            echo "<option value='{$action}' {$selected}>{$action}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">ตั้งแต่วันที่</label>
                    <input type="date" name="date_from" id="date_from" 
                           value="<?php echo $_GET['date_from'] ?? ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">ถึงวันที่</label>
                    <input type="date" name="date_to" id="date_to" 
                           value="<?php echo $_GET['date_to'] ?? ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-4 flex justify-end space-x-4">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        ค้นหา
                    </button>
                    <a href="logs.php" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        ล้างการค้นหา
                    </a>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white shadow rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่-เวลา</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้ใช้</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การกระทำ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">รายละเอียด</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($logs->num_rows > 0): ?>
                            <?php while ($log = $logs->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($log['user_id']): ?>
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <span class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($log['username']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">ไม่ระบุ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            echo match($log['action']) {
                                                'LOGIN' => 'bg-green-100 text-green-800',
                                                'LOGOUT' => 'bg-gray-100 text-gray-800',
                                                'CREATE' => 'bg-blue-100 text-blue-800',
                                                'UPDATE' => 'bg-yellow-100 text-yellow-800',
                                                'DELETE' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            ?>">
                                            <?php echo htmlspecialchars($log['action']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo htmlspecialchars($log['description']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($log['ip_address']); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    ไม่พบข้อมูล
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                แสดง <span class="font-medium"><?php echo $offset + 1; ?></span> ถึง 
                                <span class="font-medium"><?php echo min($offset + $per_page, $total_rows); ?></span> จาก 
                                <span class="font-medium"><?php echo $total_rows; ?></span> รายการ
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <a href="?page=<?php echo max($page - 1, 1); ?>" 
                                   class="<?php echo $page <= 1 ? 'disabled cursor-not-allowed' : ''; ?> relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>" 
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium 
                                              <?php echo $page == $i 
                                                    ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
                                                    : 'text-gray-500 hover:bg-gray-50'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <a href="?page=<?php echo min($page + 1, $total_pages); ?>" 
                                   class="<?php echo $page >= $total_pages ? 'disabled cursor-not-allowed' : ''; ?> relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 