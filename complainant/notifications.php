<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'complainant'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/pagination.php');
include('../includes/header.php');
include('../includes/sidebar.php');
include('../includes/notifications_page.php');
include('../includes/footer.php');
?>
