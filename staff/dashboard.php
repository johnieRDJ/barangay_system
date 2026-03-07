<?php
include('../includes/header.php');
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff'){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/database.php');
include('../includes/sidebar.php');

$user_id = $_SESSION['user_id'];

$total_assigned = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM complaints 
 WHERE assigned_staff_id='$user_id'"))['total'];

$in_progress = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM complaints 
 WHERE assigned_staff_id='$user_id' 
 AND status='in_progress'"))['total'];

$resolved = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM complaints 
 WHERE assigned_staff_id='$user_id' 
 AND status='resolved'"))['total'];

?>

<h1>Staff Dashboard</h1>

<p>View and update assigned complaints here.</p>

<div class="cards">

    <div class="card">
        <h3><?php echo $total_assigned; ?></h3>
        <p>Total Assigned</p>
    </div>

    <div class="card">
        <h3><?php echo $in_progress; ?></h3>
        <p>In Progress</p>
    </div>

    <div class="card">
        <h3><?php echo $resolved; ?></h3>
        <p>Resolved</p>
    </div>

</div>

<a href="../auth/logout.php">Logout</a>
<?php include('../includes/footer.php'); ?>