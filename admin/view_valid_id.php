<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($userId <= 0){
    http_response_code(400);
    echo 'Invalid ID request.';
    exit();
}

$profile = db_select_one(
    $conn,
    "SELECT users.firstname,
            users.lastname,
            user_profiles.valid_id_image
     FROM users
     LEFT JOIN user_profiles ON users.user_id = user_profiles.user_id
     WHERE users.user_id=?
     LIMIT 1",
    'i',
    [$userId]
);

if(!$profile || empty($profile['valid_id_image'])){
    http_response_code(404);
    echo 'No valid ID was uploaded for this user.';
    exit();
}

$uploadsDir = realpath(__DIR__ . '/../uploads/valid_ids');
$fileName = basename($profile['valid_id_image']);
$filePath = $uploadsDir ? $uploadsDir . DIRECTORY_SEPARATOR . $fileName : false;

if($uploadsDir && (!is_file($filePath) || strpos(realpath($filePath) ?: '', $uploadsDir) !== 0)){
    foreach(scandir($uploadsDir) ?: [] as $candidate){
        if(strtolower($candidate) === strtolower($fileName)){
            $filePath = $uploadsDir . DIRECTORY_SEPARATOR . $candidate;
            break;
        }
    }
}

if(!$uploadsDir || !is_file($filePath)){
    http_response_code(404);
    echo 'The uploaded ID file is missing on the server. Please re-upload the valid ID or upload the existing uploads/valid_ids files to the live server.';
    exit();
}

$mime = mime_content_type($filePath);
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
];

if(!isset($allowedTypes[$mime])){
    http_response_code(415);
    echo 'Unsupported valid ID file type.';
    exit();
}

$displayName = trim(($profile['firstname'] ?? '') . ' ' . ($profile['lastname'] ?? ''));
$downloadName = preg_replace('/[^A-Za-z0-9._-]+/', '_', ($displayName !== '' ? $displayName . '_valid_id' : 'valid_id')) . '.' . $allowedTypes[$mime];

while(ob_get_level() > 0){
    ob_end_clean();
}

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $downloadName . '"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

$handle = fopen($filePath, 'rb');
if($handle){
    while(!feof($handle)){
        echo fread($handle, 8192);
        flush();
    }
    fclose($handle);
}
exit();
?>
