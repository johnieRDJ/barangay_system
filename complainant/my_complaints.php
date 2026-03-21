<?php
include('../includes/header.php');

if($_SESSION['role'] != 'complainant'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/sidebar.php');

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,
"SELECT * FROM complaints 
 WHERE complainant_id='$user_id'
 ORDER BY complaint_id DESC");
?>

<h2>My Complaints</h2>

<table border="1" cellpadding="10" width="100%">
<tr>
    <th>Subject</th>
    <th>Description</th>
    <th>Status</th>
    <th>Staff Update</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>
<tr>

<td><?php echo $row['subject']; ?></td>

<td><?php echo $row['description']; ?></td>

<td>
<?php
if($row['status'] == 'Pending'){
    echo "<span style='color:orange;'>Pending</span>";
}
elseif($row['status'] == 'In Progress'){
    echo "<span style='color:blue;'>In Progress</span>";
}
else{
    echo "<span style='color:green;'>Resolved</span>";
}
?>
</td>

<td>
<?php
if(!empty($row['staff_comment'])){
    echo $row['staff_comment'];
} else {
    echo "<i>No update yet</i>";
}
?>
</td>

<td>
<?php if($row['status'] == 'Pending'): ?>
    <a href="edit_complaint.php?id=<?php echo $row['complaint_id']; ?>">Edit</a> |
    <a href="delete_complaint.php?id=<?php echo $row['complaint_id']; ?>">Delete</a>
<?php else: ?>
    No Action
<?php endif; ?>
</td>

</tr>
<?php endwhile; ?>

</table>

<?php include('../includes/footer.php'); ?>