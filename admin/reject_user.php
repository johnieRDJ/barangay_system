<?php
session_start();

include('../config/database.php');
include('../includes/send_account_status.php');

if($_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

$id = intval($_GET['id']); // safer

// Reject the user
mysqli_query($conn, 
"UPDATE users 
 SET account_status='rejected'
 WHERE user_id='$id'");

// Get user information
$result = mysqli_query($conn,"SELECT firstname, lastname, email FROM users WHERE user_id='$id'");
$user = mysqli_fetch_assoc($result);

// Combine first and last name
$fullname = $user['firstname']." ".$user['lastname'];

// Send rejection email
sendAccountStatus($user['email'], $fullname, "rejected");

// Save log
mysqli_query($conn, 
"INSERT INTO logs (user_id, action)
 VALUES ('".$_SESSION['user_id']."',
 'Rejected user ID $id')");

// Redirect
header("Location: manage_users.php");
exit();
?>



