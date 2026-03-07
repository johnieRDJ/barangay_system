<?php
session_start();
include('../includes/header.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/database.php');
include('../includes/sidebar.php');

// COUNT USERS
$total_users = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM users WHERE role != 'admin'"))['total'];

$pending_users = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM users WHERE account_status='pending'"))['total'];

// COUNT COMPLAINTS
$total_complaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM complaints"))['total'];

$pending_complaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM complaints WHERE status='pending'"))['total'];

$resolved_complaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM complaints WHERE status='resolved'"))['total'];

?>

<h1>Admin Dashboard</h1>
<p>Welcome to the Admin Panel.</p>

<div class="cards">

    <div class="card">
        <h3><?php echo $total_users; ?></h3>
        <p>Total Users</p>
    </div>

    <div class="card">
        <h3><?php echo $pending_users; ?></h3>
        <p>Pending Users</p>
    </div>

    <div class="card">
        <h3><?php echo $total_complaints; ?></h3>
        <p>Total Complaints</p>
    </div>

    <div class="card">
        <h3><?php echo $pending_complaints; ?></h3>
        <p>Pending Complaints</p>
    </div>

    <div class="card">
        <h3><?php echo $resolved_complaints; ?></h3>
        <p>Resolved Complaints</p>
    </div>

</div>

<a href="../auth/logout.php">Logout</a>
<?php include('../includes/footer.php'); ?>