<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'superadmin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/header.php');
include('../includes/sidebar.php');

// COUNT ADMINS
$admins = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM users WHERE role='admin'"))['total'];

// COUNT USERS
$users = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM users WHERE role != 'superadmin'"))['total'];
?>

<div class="dashboard-wrapper page-shell">

    <div class="dashboard-header">
        <h1>Superadmin Dashboard</h1>
        <p>Oversee admin accounts and the broader barangay user base.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $admins; ?></h3>
            <p>Total Admins</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $users; ?></h3>
            <p>Total Users</p>
        </div>
    </div>

</div>

<?php include('../includes/footer.php'); ?>
