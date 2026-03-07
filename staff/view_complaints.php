<?php
include('../includes/header.php');
include('../config/database.php');
include('../includes/sidebar.php');

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,
"SELECT * FROM complaints
 WHERE assigned_staff_id='$user_id'");
?>

<h2>Assigned Complaints</h2>

<table border="1" cellpadding="10">
<tr>
    <th>Subject</th>
    <th>Status</th>
    <th>Update</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?php echo $row['subject']; ?></td>
    <td><?php echo $row['status']; ?></td>
    <td>
        <a href="update_status.php?id=<?php echo $row['complaint_id']; ?>">
            Mark Resolved
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>

<?php include('../includes/footer.php'); ?>