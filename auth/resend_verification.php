<?php
include('../config/database.php');
include('../includes/send_otp.php');

$message = '';
$messageType = '';
$email = trim($_GET['email'] ?? '');

if(isset($_POST['send'])){
    $email = trim($_POST['email'] ?? '');

    $user = db_select_one($conn,
    "SELECT users.user_id,
            users.firstname,
            users.lastname,
            users.email,
            users.role,
            COALESCE(user_auth.email_verified, 0) AS email_verified
     FROM users
     LEFT JOIN user_auth ON users.user_id = user_auth.user_id
     WHERE BINARY users.email=?
     LIMIT 1",
     's',
     [$email]);

    if($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } elseif(!$user){
        $message = 'That email does not exist in the system. Please register first.';
        $messageType = 'error';
    } elseif(intval($user['email_verified']) === 1){
        $message = 'This email is already verified. You can login now.';
        $messageType = 'success';
    } else {
        $token = bin2hex(random_bytes(16));
        $userId = intval($user['user_id']);

        db_execute($conn,
        "INSERT INTO user_auth (user_id, email_verified, verification_token)
         VALUES (?, 0, ?)
         ON DUPLICATE KEY UPDATE verification_token=VALUES(verification_token)",
         'is',
         [$userId, $token]);

        $verificationLink = rtrim(APP_URL, '/') . "/auth/verify_email.php?token=" . urlencode($token);
        $fullname = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
        sendRegistrationVerificationEmail($user['email'], $fullname, $user['role'], $verificationLink);

        $message = 'A new verification email has been sent to your registered email address.';
        $messageType = 'success';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Resend Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="container">
    <h2>Resend Verification</h2>

    <p>Enter your registered email address and we will send a new verification link if your email is not verified yet.</p>

    <?php if($message !== ''): ?>
        <p style="color: <?php echo $messageType === 'success' ? '#166534' : '#b91c1c'; ?>; font-weight: 700;">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Enter your registered email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
        <button type="submit" name="send">Resend Verification Email</button>
    </form>

    <div class="link">
        <a href="login.php">Back to Login</a>
    </div>
</div>

</body>
</html>
