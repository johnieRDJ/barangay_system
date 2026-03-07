<?php
session_start();

include('../config/database.php');
include('../includes/header.php');

if($_SESSION['role'] != 'complainant'){
    header("Location: ../auth/login.php");
    exit();
}

include('../includes/sidebar.php');

if(isset($_POST['submit'])){

    // Sanitize input to prevent SQL errors
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $user_id = $_SESSION['user_id'];

    // Insert complaint
    mysqli_query($conn,
    "INSERT INTO complaints (complainant_id, subject, description)
     VALUES ('$user_id','$subject','$description')");

    // Insert log
    mysqli_query($conn,
    "INSERT INTO logs (user_id, action)
     VALUES ('$user_id','Created a complaint')");

    echo "<script>
    alert('Complaint Submitted!');
    window.location='my_complaints.php';
    </script>";
}
?>

<h2>Submit Complaint</h2>

<form method="POST">
    <input type="text" name="subject" placeholder="Subject" required>
    <textarea name="description" placeholder="Complaint Details" required></textarea>
    <button type="submit" name="submit">Submit</button>
</form>

<?php include('../includes/footer.php'); ?>
