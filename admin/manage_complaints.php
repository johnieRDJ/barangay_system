<?php
include('../includes/header.php');
include('../includes/send_complaint_update.php');

if($_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/sidebar.php');

$result = mysqli_query($conn,
"SELECT complaints.*, users.fullname
 FROM complaints
 JOIN users ON complaints.complainant_id = users.user_id");

$staff = mysqli_query($conn,
"SELECT * FROM users WHERE role='staff'");
?>

<h2>Manage Complaints</h2>

<table border="1" cellpadding="10">
<tr>
    <th>Complainant</th>
    <th>Subject</th>
    <th>Status</th>
    <th>Assign Staff</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?php echo $row['fullname']; ?></td>
    <td><?php echo $row['subject']; ?></td>
    <td><?php echo $row['status']; ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">
            <select name="staff_id">
                <?php while($s = mysqli_fetch_assoc($staff)): ?>
                    <option value="<?php echo $s['user_id']; ?>">
                        <?php echo $s['fullname']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button name="assign">Assign</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>

</table>

<?php
if(isset($_POST['assign'])){
    $complaint_id = $_POST['complaint_id'];
    $staff_id = $_POST['staff_id'];

    mysqli_query($conn,
    "UPDATE complaints
     SET assigned_staff_id='$staff_id',
         status='in_progress'
     WHERE complaint_id='$complaint_id'");

    mysqli_query($conn,
    "INSERT INTO logs (user_id, action)
     VALUES ('".$_SESSION['user_id']."',
     'Assigned staff to complaint ID $complaint_id')");
}
?>

<?php include('../includes/footer.php'); ?>