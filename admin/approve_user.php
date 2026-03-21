<?php
session_start();

include('../config/database.php');
include('../includes/send_account_status.php'); // IMPORTANT

if($_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'];

// Approve the user
mysqli_query($conn, 
"UPDATE users 
 SET account_status='approved',
     residency_status='verified'
 WHERE user_id='$id'");

// Get user info
$result = mysqli_query($conn,"SELECT firstname, lastname, email FROM users WHERE user_id='$id'");
$user = mysqli_fetch_assoc($result);

// Combine first and last name
$fullname = $user['firstname'] . " " . $user['lastname'];

// Send approval email
sendAccountStatus($user['email'], $fullname, "approved");

// Save log
mysqli_query($conn, 
"INSERT INTO logs (user_id, action)
 VALUES ('".$_SESSION['user_id']."',
 'Approved user ID $id')");

// Redirect
header("Location: manage_users.php");
exit();
?>