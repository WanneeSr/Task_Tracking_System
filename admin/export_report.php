<?php
require_once '../db.php';
require_once '../auth.php';
checkAdmin();

// ตรวจสอบประเภทรายงาน
if (!isset($_GET['type'])) {
    die("กรุณาระบุประเภทรายงาน");
}

$type = $_GET['type'];

// ฟังก์ชันสำหรับสร้าง Excel file
function generateExcel($data, $headers, $filename) {
    // กำหนด header สำหรับ Excel file
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    
    // เริ่มสร้าง HTML สำหรับ Excel
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    echo '</head>';
    echo '<body>';
    echo '<table border="1">';
    
    // สร้าง headers
    echo '<tr>';
    foreach ($headers as $header) {
        echo '<th style="background-color: #f0f0f0;">' . $header . '</th>';
    }
    echo '</tr>';
    
    // สร้างข้อมูลแต่ละแถว
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . $cell . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</body>';
    echo '</html>';
    
    exit();
}

// ดึงข้อมูลตามประเภทรายงาน
switch ($type) {
    case 'monthly':
        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks
            FROM tasks 
            WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC";
        
        $result = $mysqli->query($sql);
        $data = [];
        $headers = ['เดือน', 'งานทั้งหมด', 'เสร็จสิ้น', 'กำลังดำเนินการ', 'รอดำเนินการ', 'อัตราความสำเร็จ'];
        
        while ($row = $result->fetch_assoc()) {
            $success_rate = $row['total_tasks'] > 0 
                ? round(($row['completed_tasks'] / $row['total_tasks']) * 100, 1) 
                : 0;
            
            $data[] = [
                date('M Y', strtotime($row['month'] . '-01')),
                $row['total_tasks'],
                $row['completed_tasks'],
                $row['in_progress_tasks'],
                $row['pending_tasks'],
                $success_rate . '%'
            ];
        }
        
        generateExcel($data, $headers, 'monthly_report_' . date('Y-m-d'));
        break;

    case 'performance':
        $sql = "
            SELECT 
                u.username,
                COUNT(t.task_id) as total_assigned,
                SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) as pending_tasks
            FROM users u
            LEFT JOIN tasks t ON u.user_id = t.assigned_to
            WHERE u.role != 'admin'
            GROUP BY u.user_id
            ORDER BY completed_tasks DESC";
        
        $result = $mysqli->query($sql);
        $data = [];
        $headers = ['ผู้ใช้', 'งานทั้งหมด', 'เสร็จสิ้น', 'กำลังดำเนินการ', 'รอดำเนินการ', 'อัตราความสำเร็จ'];
        
        while ($row = $result->fetch_assoc()) {
            $success_rate = $row['total_assigned'] > 0 
                ? round(($row['completed_tasks'] / $row['total_assigned']) * 100, 1) 
                : 0;
            
            $data[] = [
                $row['username'],
                $row['total_assigned'],
                $row['completed_tasks'],
                $row['in_progress_tasks'],
                $row['pending_tasks'],
                $success_rate . '%'
            ];
        }
        
        generateExcel($data, $headers, 'performance_report_' . date('Y-m-d'));
        break;

    case 'overdue':
        $sql = "
            SELECT t.*, u.username
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.user_id
            WHERE t.status != 'completed' 
            AND t.due_date < CURRENT_DATE
            ORDER BY t.due_date ASC";
        
        $result = $mysqli->query($sql);
        $data = [];
        $headers = ['งาน', 'ผู้รับผิดชอบ', 'กำหนดส่ง', 'เลยกำหนด (วัน)', 'สถานะ'];
        
        while ($row = $result->fetch_assoc()) {
            $days_overdue = floor((strtotime('now') - strtotime($row['due_date'])) / (60 * 60 * 24));
            $status = match($row['status']) {
                'in_progress' => 'กำลังดำเนินการ',
                'pending' => 'รอดำเนินการ',
                default => 'ไม่ระบุ'
            };
            
            $data[] = [
                $row['title'],
                $row['username'] ?? 'ไม่ระบุ',
                date('d/m/Y', strtotime($row['due_date'])),
                $days_overdue,
                $status
            ];
        }
        
        generateExcel($data, $headers, 'overdue_tasks_' . date('Y-m-d'));
        break;

    default:
        include '../header.php';
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">ข้อผิดพลาด!</strong>
                <span class="block sm:inline">ไม่พบประเภทรายงานที่ระบุ</span>
              </div>';
        break;
}
?> 