<?php
session_start();

include('../config/database.php');


include('../includes/send_complaint_update.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

// HANDLE ASSIGN
if(isset($_POST['assign'])){

    $complaint_id = $_POST['complaint_id'];
    $staff_id = $_POST['staff_id'];
    $email = $_POST['email'];
    $fullname = $_POST['fullname'];
    $subject = $_POST['subject'];

    mysqli_query($conn,
    "UPDATE complaints
     SET assigned_staff_id='$staff_id',
         status='In Progress'
     WHERE complaint_id='$complaint_id'");

    sendComplaintUpdate($email, $fullname, $subject, "In Progress");

    mysqli_query($conn,
    "INSERT INTO logs (user_id, action)
     VALUES ('".$_SESSION['user_id']."',
     'Assigned staff to complaint ID $complaint_id')");

    header("Location: manage_complaints.php");
    exit();
}

include('../includes/header.php');
include('../includes/sidebar.php');

// GET COMPLAINTS + USER + STAFF
$result = mysqli_query($conn,
"SELECT complaints.*, 
        u.firstname AS fname, u.lastname AS lname, u.email,
        s.firstname AS staff_fname, s.lastname AS staff_lname
 FROM complaints
 JOIN users u ON complaints.complainant_id = u.user_id
 LEFT JOIN users s ON complaints.assigned_staff_id = s.user_id
 ORDER BY complaints.complaint_id DESC");

// GET APPROVED STAFF
$staff = mysqli_query($conn,
"SELECT * FROM users 
 WHERE role='staff' AND account_status='approved'");
?>

<h2>Manage Complaints</h2>

<table border="1" cellpadding="10" width="100%">
<tr>
    <th>Complainant</th>
    <th>Subject</th>
    <th>Description</th>
    <th>Status</th>
    <th>Assigned Staff</th>
    <th>Assign</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>

<tr>

<td><?php echo $row['fname']." ".$row['lname']; ?></td>

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
if($row['staff_fname']){
    echo $row['staff_fname']." ".$row['staff_lname'];
}else{
    echo "<i>Not Assigned</i>";
}
?>
</td>

<td>

<?php if(!$row['assigned_staff_id']){ ?>

<form method="POST">

<input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">
<input type="hidden" name="email" value="<?php echo $row['email']; ?>">
<input type="hidden" name="fullname" value="<?php echo $row['fname']." ".$row['lname']; ?>">
<input type="hidden" name="subject" value="<?php echo $row['subject']; ?>">

<select name="staff_id" required>

<?php
mysqli_data_seek($staff, 0); // 🔥 important fix
while($s = mysqli_fetch_assoc($staff)):
?>

<option value="<?php echo $s['user_id']; ?>">
<?php echo $s['firstname']." ".$s['lastname']; ?>
</option>

<?php endwhile; ?>

</select>

<button type="submit" name="assign">Assign</button>

</form>

<?php } else {
    echo "-";
} ?>

</td>

</tr>

<?php endwhile; ?>

</table>



<?php include('../includes/footer.php'); ?>

