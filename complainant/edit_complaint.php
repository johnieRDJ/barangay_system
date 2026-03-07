<?php
include('../includes/header.php');
include('../config/database.php');
include('../includes/sidebar.php');

$id = $_GET['id'];

$result = mysqli_query($conn,
"SELECT * FROM complaints WHERE complaint_id='$id'");
$data = mysqli_fetch_assoc($result);
?>

<h2>Edit Complaint</h2>

<form method="POST">
    <input type="text" name="subject" value="<?php echo $data['subject']; ?>" required>
    <textarea name="description" required><?php echo $data['description']; ?></textarea>
    <button type="submit" name="update">Update</button>
</form>

<?php
if(isset($_POST['update'])){
    $subject = $_POST['subject'];
    $description = $_POST['description'];

    mysqli_query($conn,
    "UPDATE complaints
     SET subject='$subject', description='$description'
     WHERE complaint_id='$id'");

    mysqli_query($conn,
    "INSERT INTO logs (user_id, action)
     VALUES ('".$_SESSION['user_id']."','Updated complaint ID $id')");

    echo "<script>window.location='my_complaints.php';</script>";
}
?>

<?php include('../includes/footer.php'); ?>