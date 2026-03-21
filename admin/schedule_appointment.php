<?php
session_start();

include('../includes/header.php');
include('../includes/send_residency_schedule.php');





if($_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/sidebar.php');

$user_id = $_GET['id'];
?>

<h2>Schedule Residency Appointment</h2>

<form method="POST">
    <input type="datetime-local" name="appointment_date" required>
    <button type="submit" name="schedule">Schedule</button>
</form>

<?php
if(isset($_POST['schedule'])){

    $date = $_POST['appointment_date'];

    // Save appointment to database
    mysqli_query($conn,
    "INSERT INTO appointments (user_id, appointment_date, purpose)
     VALUES ('$user_id','$date','Barangay Residency Verification')");

    // Get user information
    $result = mysqli_query($conn, "SELECT firstname, lastname, email FROM users WHERE user_id='$user_id'");
    $user = mysqli_fetch_assoc($result);

    $fullname = $user['firstname']." ".$user['lastname'];
    $email = $user['email'];

    // Format date nicely for email
    $formatted_date = date("F d, Y - g:i A", strtotime($date));

    // Send email notification
    sendResidencySchedule($email, $fullname, $formatted_date);

    // Save log
    mysqli_query($conn,
    "INSERT INTO logs (user_id, action)
     VALUES ('".$_SESSION['user_id']."',
     'Scheduled residency appointment for user ID $user_id')");

    echo "<script>
    alert('Appointment Scheduled and Email Sent!');
    window.location='manage_users.php';
    </script>";
}
?>

<?php include('../includes/footer.php'); ?>

