<?php
session_start();
if(!empty($_SESSION['temp_valid_id'])){
    $tempPath = '../uploads/valid_ids/temp/' . $_SESSION['temp_valid_id'];
    if(file_exists($tempPath)){
        @unlink($tempPath);
    }
}
unset($_SESSION['temp_valid_id'], $_SESSION['temp_valid_id_name']);
$ref = $_SERVER['HTTP_REFERER'] ?? 'register.php';
$refPath = parse_url($ref, PHP_URL_PATH);
if(basename($refPath) === 'register.php'){
    header("Location: register.php");
} else {
    header("Location: register.php");
}
exit();
?>