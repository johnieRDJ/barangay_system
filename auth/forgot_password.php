<?php
include('../config/database.php');
include('../includes/send_reset.php');

$message = '';
$message_type = '';

if(isset($_POST['send'])){

    $email_input = trim($_POST['email']);

    db_execute($conn,
    "DELETE FROM password_resets
     WHERE reset_expiry < NOW()");

    //  CHECK USER
    $user = db_select_one($conn,
    "SELECT users.user_id,
            users.firstname,
            users.lastname,
            users.email,
            COALESCE(user_auth.email_verified, 0) AS email_verified
     FROM users
     LEFT JOIN user_auth ON users.user_id = user_auth.user_id
     WHERE BINARY email=?
     LIMIT 1",
     's',
     [$email_input]);

    if(!$user){
        $message = "That email does not exist in the system. Please register first.";
        $message_type = 'error';
    } elseif(intval($user['email_verified']) !== 1){
        $message = "Please verify your registered email first before requesting a password reset.";
        $message_type = 'error';
    } else {
        $user_id = intval($user['user_id']);
        $fullname = trim($user['firstname'] . ' ' . $user['lastname']);

        //  GENERATE TOKEN
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        //  DELETE OLD TOKENS (IMPORTANT )
        db_execute($conn,
        "DELETE FROM password_resets WHERE user_id=?",
        'i',
        [$user_id]);

        //  INSERT NEW TOKEN
        db_execute($conn,
        "INSERT INTO password_resets (user_id, reset_token, reset_expiry)
         VALUES (?, ?, ?)",
         'iss',
         [$user_id, $token, $expiry]);

        //  SEND EMAIL
        sendResetLink($user['email'], $fullname, $token);

        $message = "A password reset link has been sent to your registered email.";
        $message_type = 'success';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="container">
    <h2>Forgot Password</h2>

    <p>Enter the email address registered to your account. Your email must be verified before you can reset your password.</p>

    <?php if($message != ''): ?>
    <p style="color: <?php echo $message_type === 'success' ? '#166534' : '#b91c1c'; ?>; font-weight: 700;">
        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
    </p>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Enter your registered email" required>
        <button name="send">Send Reset Link</button>
    </form>

    <div class="link">
        <a href="login.php">Back to Login</a>
    </div>
</div>

</body>
</html>
