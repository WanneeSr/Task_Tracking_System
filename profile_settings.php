<?php
// profile_settings.php
include 'header.php';
?>

<div class="container mt-5">
    <h1>Profile & Settings</h1>
    <form id="profileForm">
        <div class="mb-3">
            <label for="username" class="form-label">ชื่อผู้ใช้</label>
            <input type="text" class="form-control" id="username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" class="form-control" id="password" required>
        </div>
        <button type="submit" class="btn btn-primary">บันทึก</button>
    </form>
</div>

<script>
    // อัปเดตโปรไฟล์
    document.getElementById('profileForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        fetch('/api/profile', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: username,
                password: password,
            }),
        })
        .then(response => response.json())
        .then(data => {
            alert('Profile updated successfully!');
        });
    });
</script>

<?php
include 'footer.php';
?>