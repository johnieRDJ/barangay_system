<?php
include('../includes/header.php');
include('../config/database.php');
include('../includes/sidebar.php');
include('../includes/send_complaint_update.php');

if($_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}





$result = mysqli_query($conn,
"SELECT complaints.*, users.firstname, users.lastname, users.email
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

<td>
<?php echo $row['firstname']." ".$row['lastname']; ?>
</td>

<td><?php echo $row['subject']; ?></td>

<td><?php echo $row['status']; ?></td>

<td>

<form method="POST">

<input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">
<input type="hidden" name="email" value="<?php echo $row['email']; ?>">
<input type="hidden" name="fullname" value="<?php echo $row['firstname']." ".$row['lastname']; ?>">
<input type="hidden" name="subject" value="<?php echo $row['subject']; ?>">

<select name="staff_id" required>

<?php mysqli_data_seek($staff,0); while($s = mysqli_fetch_assoc($staff)): ?>

<option value="<?php echo $s['user_id']; ?>">
<?php echo $s['firstname']." ".$s['lastname']; ?>
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
    $email = $_POST['email'];
    $fullname = $_POST['fullname'];
    $subject = $_POST['subject'];

    // Update complaint
    mysqli_query($conn,
    "UPDATE complaints
     SET assigned_staff_id='$staff_id',
         status='In Progress'
     WHERE complaint_id='$complaint_id'");

    // Send email notification
    sendComplaintUpdate(
        $email,
        $fullname,
        $subject,
        "In Progress"
    );

    // Save log
    mysqli_query($conn,
    "INSERT INTO logs (user_id, action)
     VALUES ('".$_SESSION['user_id']."',
     'Assigned staff to complaint ID $complaint_id')");

    echo "<script>
    alert('Staff assigned successfully!');
    window.location='manage_complaints.php';
    </script>";
}
?>

<?php include('../includes/footer.php'); ?>
