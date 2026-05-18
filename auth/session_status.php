<?php
session_start();

if(!headers_sent()){
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

echo json_encode([
    'authenticated' => isset($_SESSION['user_id'], $_SESSION['role']),
    'role' => $_SESSION['role'] ?? null,
]);
exit();
?>
