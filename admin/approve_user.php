<?php
session_start();

include('../config/database.php');
include('../includes/send_account_status.php'); // IMPORTANT
require_once __DIR__ . '/../includes/notifications.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

$id = intval($_GET['id']);

$target = db_select_one($conn,
"SELECT role FROM users WHERE user_id=? LIMIT 1",
'i',
[$id]);

if($target && $target['role'] == 'superadmin'){
    echo "<script>alert('Superadmin account is protected.'); window.location='manage_users.php';</script>";
    exit();
}

// Approve the user
db_execute($conn,
"UPDATE users 
 SET account_status='approved'
 WHERE user_id=? AND role!='superadmin'",
 'i',
 [$id]);

db_execute($conn,
"INSERT INTO residency (user_id, status)
 SELECT ?, 'pending'
 WHERE NOT EXISTS (
     SELECT 1 FROM residency WHERE user_id=?
 )",
 'ii',
 [$id, $id]);

// Get user info
$user = db_select_one($conn,
"SELECT firstname, lastname, email FROM users WHERE user_id=? LIMIT 1",
'i',
[$id]);

if(!$user){
    header("Location: manage_users.php");
    exit();
}

// Combine first and last name
$fullname = $user['firstname'] . " " . $user['lastname'];

// Send approval email
sendAccountStatus($user['email'], $fullname, "approved");

notify_user(
    $conn,
    $id,
    'Account Approved',
    'Your account has been approved. Please complete your My Profile information, including your address, phone number, birthdate, civil status, valid ID, and profile picture.',
    '../' . ($target['role'] ?? 'complainant') . '/profile.php'
);

// Save log
db_execute($conn,
"INSERT INTO logs (user_id, action)
 VALUES (?, ?)",
 'is',
 [intval($_SESSION['user_id']), "Approved user ID $id"]);

$_SESSION['status_message'] = $fullname . " has been approved. Residency verification is still separate.";

// Redirect
header("Location: manage_users.php");
exit();
?>
