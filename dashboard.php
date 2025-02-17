<?php
// dashboard.php
include 'header.php';

require_once 'db.php';
require_once 'auth.php';

// ดึงข้อมูลสถิติงานตามสถานะ
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$stats_sql = match($role) {
    'admin' => "SELECT 
                status,
                COUNT(*) as count,
                COUNT(*) * 100.0 / (SELECT COUNT(*) FROM tasks) as percentage
               FROM tasks 
               GROUP BY status",
    'manager' => "SELECT 
                  status,
                  COUNT(*) as count,
                  COUNT(*) * 100.0 / (SELECT COUNT(*) FROM tasks WHERE created_by = ?) as percentage
                 FROM tasks 
                 WHERE created_by = ?
                 GROUP BY status",
    'employee' => "SELECT 
                   status,
                   COUNT(*) as count,
                   COUNT(*) * 100.0 / (SELECT COUNT(*) FROM tasks WHERE assigned_to = ?) as percentage
                  FROM tasks 
                  WHERE assigned_to = ?
                  GROUP BY status",
    default => null
};

$stats = [];
if ($stats_sql) {
    $stmt = $conn->prepare($stats_sql);
    if ($role !== 'admin') {
        $stmt->bind_param("ii", $user_id, $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $stats[$row['status']] = [
            'count' => $row['count'],
            'percentage' => round($row['percentage'], 1)
        ];
    }
}

// ดึงข้อมูลสถิติตามความสำคัญ
$priority_sql = str_replace('status', 'priority', $stats_sql);
$priority_stats = [];
if ($priority_sql) {
    $stmt = $conn->prepare($priority_sql);
    if ($role !== 'admin') {
        $stmt->bind_param("ii", $user_id, $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $priority_stats[$row['priority']] = [
            'count' => $row['count'],
            'percentage' => round($row['percentage'], 1)
        ];
    }
}
?>

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">แดชบอร์ด</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- กราฟสถิติ -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b px-4 py-3">
                <h5 class="font-semibold text-gray-900">สถิติงาน</h5>
            </div>
            <div class="p-4">
                <canvas id="taskStatsChart"></canvas>
            </div>
        </div>

        <!-- กราฟวงกลมแสดงสถานะงาน -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b px-4 py-3">
                <h5 class="font-semibold text-gray-900">สถานะงาน</h5>
            </div>
            <div class="p-4">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- กราฟวงกลมแสดงความสำคัญของงาน -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b px-4 py-3">
                <h5 class="font-semibold text-gray-900">ความสำคัญของงาน</h5>
            </div>
            <div class="p-4">
                <canvas id="priorityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ตารางสรุปสถิติ -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b px-4 py-3">
            <h5 class="font-semibold text-gray-900">สรุปสถิติงาน</h5>
        </div>
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                หมวดหมู่
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                จำนวน
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                เปอร์เซ็นต์
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- สถานะงาน -->
                        <tr class="bg-indigo-50">
                            <td colspan="3" class="px-6 py-3">
                                <strong>สถานะงาน</strong>
                            </td>
                        </tr>
                        <?php foreach ($stats as $status => $data): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo match($status) {
                                    'pending' => 'รอดำเนินการ',
                                    'in_progress' => 'กำลังดำเนินการ',
                                    'completed' => 'เสร็จสิ้น',
                                    'cancelled' => 'ยกเลิก',
                                    default => $status
                                }; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $data['count']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $data['percentage']; ?>%
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <!-- ความสำคัญของงาน -->
                        <tr class="bg-green-50">
                            <td colspan="3" class="px-6 py-3">
                                <strong>ความสำคัญของงาน</strong>
                            </td>
                        </tr>
                        <?php foreach ($priority_stats as $priority => $data): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo match($priority) {
                                    'low' => 'ต่ำ',
                                    'medium' => 'ปานกลาง',
                                    'high' => 'สูง',
                                    'urgent' => 'เร่งด่วน',
                                    default => $priority
                                }; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $data['count']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $data['percentage']; ?>%
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // กราฟสถิติ
    const ctx = document.getElementById('taskStatsChart').getContext('2d');
    const taskStatsChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['เสร็จแล้ว', 'ค้างอยู่', 'ล่าช้า'],
            datasets: [{
                label: 'จำนวนงาน',
                data: [12, 5, 3],
                backgroundColor: ['#4CAF50', '#FFC107', '#F44336'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: false
                }
            }
        }
    });

    // สร้างกราฟวงกลมแสดงสถานะงาน
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_map(function($status) {
                return match($status) {
                    'pending' => 'รอดำเนินการ',
                    'in_progress' => 'กำลังดำเนินการ',
                    'completed' => 'เสร็จสิ้น',
                    'cancelled' => 'ยกเลิก',
                    default => $status
                };
            }, array_keys($stats))); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($stats, 'count')); ?>,
                backgroundColor: ['#6c757d', '#007bff', '#28a745', '#dc3545'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // สร้างกราฟวงกลมแสดงความสำคัญของงาน
    const priorityCtx = document.getElementById('priorityChart').getContext('2d');
    new Chart(priorityCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_map(function($priority) {
                return match($priority) {
                    'low' => 'ต่ำ',
                    'medium' => 'ปานกลาง',
                    'high' => 'สูง',
                    'urgent' => 'เร่งด่วน',
                    default => $priority
                };
            }, array_keys($priority_stats))); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($priority_stats, 'count')); ?>,
                backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

<?php
include 'footer.php';
?>