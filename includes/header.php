<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Barangay Digital Complaint Desk</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body></body>