<?php
session_start();

include('../includes/header.php');
include('../config/database.php');
include('../includes/sidebar.php');

$user_id = $_SESSION['user_id'];

// ✅ LOG: Viewed assigned complaints
mysqli_query($conn,
"INSERT INTO logs (user_id, action)
 VALUES ('$user_id','Viewed assigned complaints')");

$result = mysqli_query($conn,
"SELECT * FROM complaints
 WHERE assigned_staff_id='$user_id'");
?>

<h2>Assigned Complaints</h2>

<table border="1" cellpadding="10" width="100%">
<tr>
    <th>Subject</th>
    <th>Description</th>
    <th>Status</th>
    <th>Staff Comment</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>

<tr>
    <td><?php echo $row['subject']; ?></td>

    <td><?php echo $row['description']; ?></td>

    <td><?php echo $row['status']; ?></td>

    <td>
        <?php 
        if($row['staff_comment']){
            echo $row['staff_comment'];
        } else {
            echo "No comment yet";
        }
        ?>
    </td>

    <td>

    <form method="POST">

        <input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">

        <textarea name="comment" placeholder="Enter action taken..." required></textarea><br><br>

        <button name="update">Update & Resolve</button>

    </form>

    </td>
</tr>

<?php endwhile; ?>

</table>

<?php
if(isset($_POST['update'])){

    $id = $_POST['complaint_id'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    mysqli_query($conn,
    "UPDATE complaints
     SET status='Resolved',
         staff_comment='$comment'
     WHERE complaint_id='$id'");

    // ✅ LOG: Detailed action
    mysqli_query($conn,
    "INSERT INTO logs (user_id, action)
     VALUES ('$user_id',
     'Resolved complaint ID $id and added comment')");

    echo "<script>
    alert('Complaint updated!');
    window.location='view_complaints.php';
    </script>";
}
?>

<?php include('../includes/footer.php'); ?>

