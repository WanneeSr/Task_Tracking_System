<?php
// notifications.php
include 'header.php';
?>

<div class="container mt-5">
    <h1>Notifications</h1>
    <div class="list-group" id="notificationList">
        <!-- แจ้งเตือนจะถูกเพิ่มโดย JavaScript -->
    </div>
</div>

<script>
    // ดึงข้อมูลแจ้งเตือนจาก API
    fetch('/api/notifications')
        .then(response => response.json())
        .then(data => {
            const notificationList = document.getElementById('notificationList');
            data.notifications.forEach(notification => {
                const notificationElement = document.createElement('a');
                notificationElement.href = '#';
                notificationElement.className = 'list-group-item list-group-item-action';
                notificationElement.textContent = notification.message;
                notificationList.appendChild(notificationElement);
            });
        });
</script>

<?php
include 'footer.php';
?>