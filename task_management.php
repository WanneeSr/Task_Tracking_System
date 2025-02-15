<?php
// task_management.php
include 'header.php';
?>

<div class="container mt-5">
    <h1>Task Management</h1>
    <div class="row">
        <div class="col-md-4">
            <h2>To Do</h2>
            <div class="list-group" id="todoTasks" ondrop="drop(event)" ondragover="allowDrop(event)">
                <!-- งานที่ต้องทำ -->
            </div>
        </div>
        <div class="col-md-4">
            <h2>In Progress</h2>
            <div class="list-group" id="inProgressTasks" ondrop="drop(event)" ondragover="allowDrop(event)">
                <!-- งานที่กำลังทำ -->
            </div>
        </div>
        <div class="col-md-4">
            <h2>Done</h2>
            <div class="list-group" id="doneTasks" ondrop="drop(event)" ondragover="allowDrop(event)">
                <!-- งานที่เสร็จแล้ว -->
            </div>
        </div>
    </div>
</div>

<script>
    // ฟังก์ชัน Drag & Drop
    function allowDrop(event) {
        event.preventDefault();
    }

    function drag(event) {
        event.dataTransfer.setData("text", event.target.id);
    }

    function drop(event) {
        event.preventDefault();
        const taskId = event.dataTransfer.getData("text");
        const task = document.getElementById(taskId);
        event.target.appendChild(task);

        // อัปเดตสถานะงานใน API
        const newStatus = event.target.id; // เช่น todoTasks, inProgressTasks, doneTasks
        fetch(`/api/tasks/${taskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                status: newStatus,
            }),
        });
    }

    // ดึงข้อมูลงานจาก API
    fetch('/api/tasks')
        .then(response => response.json())
        .then(data => {
            const todoTasks = document.getElementById('todoTasks');
            const inProgressTasks = document.getElementById('inProgressTasks');
            const doneTasks = document.getElementById('doneTasks');

            data.tasks.forEach(task => {
                const taskElement = document.createElement('div');
                taskElement.className = 'list-group-item';
                taskElement.id = task.id;
                taskElement.draggable = true;
                taskElement.ondragstart = drag;
                taskElement.textContent = task.name;

                if (task.status === 'todoTasks') {
                    todoTasks.appendChild(taskElement);
                } else if (task.status === 'inProgressTasks') {
                    inProgressTasks.appendChild(taskElement);
                } else if (task.status === 'doneTasks') {
                    doneTasks.appendChild(taskElement);
                }
            });
        });
</script>

<?php
include 'footer.php';
?>