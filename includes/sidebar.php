<?php
require_once __DIR__ . '/notifications.php';
$sidebarNotificationCount = (isset($_SESSION['user_id'], $conn) && $conn instanceof mysqli)
    ? notification_unread_count($conn, intval($_SESSION['user_id']))
    : 0;
?>
<div class="sidebar" id="appSidebar">
    <div class="sidebar-header">
        <h2 class="sidebar-brand">Barangay Digital Complaint Desk System</h2>
        <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-expanded="false" aria-controls="sidebarMenu">
            <span class="sidebar-toggle-icon">&#9776;</span>
            <span>Menu</span>
        </button>
    </div>

    <nav class="sidebar-menu" id="sidebarMenu">
        <?php if($_SESSION['role'] == 'superadmin'): ?>

            <a href="../superadmin/dashboard.php">Dashboard</a>
            <a href="../superadmin/manage_admins.php">Manage Admins</a>
            <a href="../superadmin/admin_requests.php">Admin Requests</a>
            <a href="../superadmin/add_admin.php">Add Admin</a>
            <a href="../admin/profile.php">My Profile</a>
            <a href="../superadmin/system_logs.php">System Logs</a>

        <?php elseif($_SESSION['role'] == 'admin'): ?>

            <a href="../admin/profile.php">My Profile</a>
            <a href="../admin/notifications.php">Notifications<?php if($sidebarNotificationCount > 0): ?> <span class="nav-badge"><?php echo $sidebarNotificationCount; ?></span><?php endif; ?></a>
            <a href="../admin/dashboard.php">Dashboard</a>
            <a href="../admin/dashboard.php#about-developer">About Developer</a>
            <a href="../admin/manage_users.php">Manage Users</a>
            <a href="../admin/manage_complaints.php">Manage Complaints</a>
            <a href="../admin/announcements.php">Announcements</a>
            <a href="../admin/view_logs.php">System Logs</a>
            <a href="../admin/view_profiles.php">User Profiles</a>

        <?php elseif($_SESSION['role'] == 'staff'): ?>

            <a href="../staff/profile.php">My Profile</a>
            <a href="../staff/notifications.php">Notifications<?php if($sidebarNotificationCount > 0): ?> <span class="nav-badge"><?php echo $sidebarNotificationCount; ?></span><?php endif; ?></a>
            <a href="../staff/dashboard.php">Dashboard</a>
            <a href="../staff/view_complaints.php">View Complaints</a>
            <a href="../staff/view_logs.php">My Logs</a>

        <?php elseif($_SESSION['role'] == 'complainant'): ?>

            <a href="../complainant/profile.php">My Profile</a>
            <a href="../complainant/notifications.php">Notifications<?php if($sidebarNotificationCount > 0): ?> <span class="nav-badge"><?php echo $sidebarNotificationCount; ?></span><?php endif; ?></a>
            <a href="../complainant/dashboard.php">Dashboard</a>
            <a href="../complainant/create_complaint.php">Submit Complaint</a>
            <a href="../complainant/my_complaints.php">My Complaints</a>

        <?php endif; ?>

        <a href="../auth/logout.php" class="logout-link" data-confirm-message="Are you sure you want to logout?">Logout</a>
    </nav>
</div>

<script>
    (function () {
        var sidebar = document.getElementById('appSidebar');
        var toggle = document.getElementById('sidebarToggle');

        if (!sidebar || !toggle) {
            return;
        }

        toggle.addEventListener('click', function () {
            var isOpen = sidebar.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    })();
</script>

<div class="main">
    <div class="topbar">
        Welcome, <?php echo htmlspecialchars($_SESSION['role']); ?>
    </div>
