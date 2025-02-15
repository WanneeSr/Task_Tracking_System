<?php
// dashboard.php
include 'header.php';
?>

<div class="container mt-5">
    <h1>Dashboard</h1>
    <div class="row">
        <!-- กราฟสถิติ -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    สถิติงาน
                </div>
                <div class="card-body">
                    <canvas id="taskStatsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- To-Do List -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    To-Do List
                </div>
                <div class="card-body">
                    <ul class="list-group" id="todoList">
                        <!-- งานจะถูกเพิ่มโดย JavaScript -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ดึงข้อมูลจาก API
    fetch('/api/tasks')
        .then(response => response.json())
        .then(data => {
            const todoList = document.getElementById('todoList');
            data.tasks.forEach(task => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.textContent = task.name;
                todoList.appendChild(li);
            });
        });

    // กราฟสถิติ
    const ctx = document.getElementById('taskStatsChart').getContext('2d');
    const taskStatsChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['เสร็จแล้ว', 'ค้างอยู่', 'ล่าช้า'],
            datasets: [{
                label: 'จำนวนงาน',
                data: [12, 5, 3], // ข้อมูลจาก API
                backgroundColor: ['#4CAF50', '#FFC107', '#F44336'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'สถิติงาน'
                }
            }
        }
    });
</script>

<?php
include 'footer.php';
?>