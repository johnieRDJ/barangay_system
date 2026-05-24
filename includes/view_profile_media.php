<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');

$type = $_GET['type'] ?? '';
$userId = intval($_SESSION['user_id']);

$mediaMap = [
    'profile' => [
        'column' => 'profile_image',
        'dir' => 'profile',
        'fallback' => 'No profile image was uploaded.',
    ],
    'valid_id' => [
        'column' => 'valid_id_image',
        'dir' => 'valid_ids',
        'fallback' => 'No valid ID was uploaded.',
    ],
    'signature' => [
        'column' => 'signature_image',
        'dir' => 'signatures',
        'fallback' => 'No e-signature was uploaded.',
    ],
];

if(!isset($mediaMap[$type])){
    http_response_code(400);
    echo 'Invalid media request.';
    exit();
}

$column = $mediaMap[$type]['column'];
$profile = db_select_one(
    $conn,
    "SELECT $column AS media_file
     FROM user_profiles
     WHERE user_id=?
     LIMIT 1",
    'i',
    [$userId]
);

if(!$profile || empty($profile['media_file'])){
    http_response_code(404);
    echo $mediaMap[$type]['fallback'];
    exit();
}

$uploadsDir = realpath(__DIR__ . '/../uploads/' . $mediaMap[$type]['dir']);
$fileName = basename($profile['media_file']);
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
    echo 'The uploaded file is missing on the server. Please upload or replace this file again.';
    exit();
}

$mime = mime_content_type($filePath);
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
];

if(!isset($allowedTypes[$mime])){
    http_response_code(415);
    echo 'Unsupported image file type.';
    exit();
}

while(ob_get_level() > 0){
    ob_end_clean();
}

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $type . '.' . $allowedTypes[$mime] . '"');
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
