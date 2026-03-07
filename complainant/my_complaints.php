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
"SELECT * FROM complaints WHERE complainant_id='$user_id'");
?>

<h2>My Complaints</h2>

<table border="1" cellpadding="10">
<tr>
    <th>Subject</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?php echo $row['subject']; ?></td>
    <td><?php echo $row['status']; ?></td>
    <td>
        <?php if($row['status'] == 'pending'): ?>
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