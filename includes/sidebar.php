<div class="sidebar">
    <h2>Barangay Desk</h2>

    <?php if($_SESSION['role'] == 'admin'): ?>

        <a href="../admin/dashboard.php">Dashboard</a>
        <a href="../admin/manage_users.php">Manage Users</a>
        <a href="../admin/manage_complaints.php">Manage Complaints</a>
        <a href="../admin/view_logs.php">System Logs</a>

    <?php elseif($_SESSION['role'] == 'staff'): ?>

        <a href="../staff/dashboard.php">Dashboard</a>
        <a href="../staff/view_complaints.php">View Complaints</a>

    <?php else: ?>

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