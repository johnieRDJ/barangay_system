<?php
session_start();
include('../config/database.php');

$id = $_GET['id'];

mysqli_query($conn,
"DELETE FROM complaints WHERE complaint_id='$id'");

mysqli_query($conn,
"INSERT INTO logs (user_id, action)
 VALUES ('".$_SESSION['user_id']."','Deleted complaint ID $id')");

header("Location: my_complaints.php");
?>