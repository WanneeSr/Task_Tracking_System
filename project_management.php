<?php
// project_management.php
include 'header.php';
?>

<div class="container mt-5">
    <h1>Project Management</h1>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createProjectModal">Create Project</button>
    <table class="table" id="projectTable">
        <thead>
            <tr>
                <th>ชื่อโปรเจค</th>
                <th>รายละเอียด</th>
                <th>วันที่เริ่มต้น</th>
                <th>วันที่สิ้นสุด</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <!-- ข้อมูลโปรเจคจะถูกเพิ่มโดย JavaScript -->
        </tbody>
    </table>
</div>

<!-- Modal สำหรับสร้างโปรเจค -->
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-labelledby="createProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createProjectModalLabel">Create Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createProjectForm">
                    <div class="mb-3">
                        <label for="projectName" class="form-label">ชื่อโปรเจค</label>
                        <input type="text" class="form-control" id="projectName" required>
                    </div>
                    <div class="mb-3">
                        <label for="projectDescription" class="form-label">รายละเอียด</label>
                        <textarea class="form-control" id="projectDescription" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="startDate" class="form-label">วันที่เริ่มต้น</label>
                        <input type="date" class="form-control" id="startDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
                        <input type="date" class="form-control" id="endDate" required>
                    </div>
                    <button type="submit" class="btn btn-primary">สร้างโปรเจค</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // สร้างโปรเจคใหม่
    document.getElementById('createProjectForm').addEventListener('submit', function (e) {
        e.preventDefault(); // ป้องกันการรีโหลดหน้า

        // ดึงค่าจากฟอร์ม
        const projectName = document.getElementById('projectName').value;
        const projectDescription = document.getElementById('projectDescription').value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        // ตรวจสอบว่าข้อมูลครบถ้วน
        if (!projectName || !projectDescription || !startDate || !endDate) {
            alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            return;
        }

        // ส่งข้อมูลไปยัง API
        fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: projectName,
                description: projectDescription,
                start_date: startDate,
                end_date: endDate,
            }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('เกิดข้อผิดพลาดในการสร้างโปรเจค');
            }
            return response.json();
        })
        .then(data => {
            alert('สร้างโปรเจคสำเร็จ!');
            window.location.reload(); // รีโหลดหน้าเพื่อแสดงโปรเจคใหม่
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาด: ' + error.message);
        });
    });
</script>

<script>
    // ฟังก์ชันเพิ่มแถวใหม่ในตาราง
    function addProjectToTable(project) {
        const projectTable = document.getElementById('projectTable').getElementsByTagName('tbody')[0];
        const row = projectTable.insertRow();
        row.innerHTML = `
            <td>${project.name}</td>
            <td>${project.description}</td>
            <td>${project.start_date}</td>
            <td>${project.end_date}</td>
            <td>
                <button class="btn btn-sm btn-warning">Edit</button>
                <button class="btn btn-sm btn-danger">Delete</button>
            </td>
        `;
    }

    // ดึงข้อมูลโปรเจคจาก API
    fetch('/api/projects')
        .then(response => response.json())
        .then(data => {
            const projectTable = document.getElementById('projectTable').getElementsByTagName('tbody')[0];
            data.projects.forEach(project => {
                addProjectToTable(project);
            });
        });

    // สร้างโปรเจคใหม่ (ปรับปรุงจากเดิม)
    document.getElementById('createProjectForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const projectName = document.getElementById('projectName').value;
        const projectDescription = document.getElementById('projectDescription').value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (!projectName || !projectDescription || !startDate || !endDate) {
            alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            return;
        }

        fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: projectName,
                description: projectDescription,
                start_date: startDate,
                end_date: endDate,
            }),
        })
        .then(response => response.json())
        .then(data => {
            alert('สร้างโปรเจคสำเร็จ!');
            addProjectToTable(data); // เพิ่มโปรเจคใหม่ในตาราง
            document.getElementById('createProjectForm').reset(); // รีเซ็ตฟอร์ม
            bootstrap.Modal.getInstance(document.getElementById('createProjectModal')).hide(); // ปิด Modal
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาด: ' + error.message);
        });
    });
</script>

<?php
include 'footer.php';
?>