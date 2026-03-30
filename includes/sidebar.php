<div class="sidebar">
    <h2>Barangay Desk</h2>

    <?php if($_SESSION['role'] == 'admin'): ?>

        <li><a href="../admin/profile.php">My Profile</a></li>
        <a href="../admin/dashboard.php">Dashboard</a>
        <a href="../admin/manage_users.php">Manage Users</a>
        <a href="../admin/manage_complaints.php">Manage Complaints</a>
        <a href="../admin/view_logs.php">System Logs</a>
        <a href="../admin/view_profiles.php">User Profiles</a>

    <?php elseif($_SESSION['role'] == 'staff'): ?>

        <li><a href="../staff/profile.php">My Profile</a></li>
        <a href="../staff/dashboard.php">Dashboard</a>
        <a href="../staff/view_complaints.php">View Complaints</a>
        <li><a href="../staff/view_logs.php">My Logs</a></li>

    <?php else: ?>

        <li><a href="../complainant/profile.php">My Profile</a></li>
        <a href="../complainant/dashboard.php">Dashboard</a>
        <a href="../complainant/create_complaint.php">Submit Complaint</a>
        <a href="../complainant/my_complaints.php">My Complaints</a>

    <?php endif; ?>

    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">
    <div class="topbar">
        Welcome, <?php echo $_SESSION['role']; ?>
    </div>