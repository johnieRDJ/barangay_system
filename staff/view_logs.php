<?php
session_start();

include('../config/database.php');
include('../includes/header.php');
include('../includes/sidebar.php');

// 🔴 ONLY STAFF CAN ACCESS
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'staff'){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 🔴 GET ONLY THIS STAFF'S LOGS
$result = mysqli_query($conn,
"SELECT logs.*, users.firstname, users.lastname
 FROM logs
 JOIN users ON logs.user_id = users.user_id
 WHERE logs.user_id = '$user_id'
 ORDER BY logs.log_time DESC");
?>

<h2>My Activity Logs</h2>

<table border="1" cellpadding="10" width="100%">

<tr>
    <th>Staff</th>
    <th>Action</th>
    <th>Date & Time</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>

<tr>

<td>
<?php echo $row['firstname']." ".$row['lastname']; ?>
</td>

<td>
<?php echo $row['action']; ?>
</td>

<td>
<?php echo $row['log_time']; ?>
</td>

</tr>

<?php endwhile; ?>

</table>

<?php include('../includes/footer.php'); ?>