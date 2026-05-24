<?php
header('Content-Type: application/json');

include('../config/database.php');

$email = trim($_POST['email'] ?? $_GET['email'] ?? '');

if($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode([
        'ok' => false,
        'exists' => false,
        'message' => 'Please enter a valid email address.'
    ]);
    exit();
}

$existingUser = db_select_one(
    $conn,
    "SELECT user_id FROM users WHERE email=? LIMIT 1",
    's',
    [$email]
);

echo json_encode([
    'ok' => true,
    'exists' => (bool) $existingUser,
    'message' => $existingUser ? 'That email is already registered. Please log in or use another email.' : ''
]);
exit();
?>
