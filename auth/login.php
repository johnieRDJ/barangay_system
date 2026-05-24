<?php
session_start();

include('../config/database.php');
include('../includes/send_otp.php');

function dashboardPathForRole($role){
    if($role == 'superadmin'){
        return '../superadmin/dashboard.php';
    }
    if($role == 'admin'){
        return '../admin/dashboard.php';
    }
    if($role == 'staff'){
        return '../staff/dashboard.php';
    }

    return '../complainant/dashboard.php';
}

if(isset($_SESSION['user_id'])){
    header("Location: " . dashboardPathForRole($_SESSION['role']));
    exit();
}

$login_error = '';
$email = '';

if(isset($_POST['login'])){
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = db_select_one(
        $conn,
        "SELECT users.*,
                COALESCE(user_auth.email_verified, 0) AS email_verified,
                COALESCE(user_auth.failed_login_attempts, 0) AS failed_login_attempts,
                user_auth.require_otp_until
         FROM users
         LEFT JOIN user_auth ON users.user_id = user_auth.user_id
         WHERE BINARY users.email=?
         LIMIT 1",
        's',
        [$email]
    );

    if(!$user){
        unset($_SESSION['temp_user']);
        $login_error = 'That account does not exist in the system. Please register first.';
    } elseif(password_verify($password, $user['password'])){
        if(intval($user['email_verified']) !== 1){
            $token = bin2hex(random_bytes(16));
            $userId = intval($user['user_id']);

            db_execute(
                $conn,
                "UPDATE user_auth
                 SET verification_token=?
                 WHERE user_id=?",
                'si',
                [$token, $userId]
            );

            $verificationLink = rtrim(APP_URL, '/') . "/auth/verify_email.php?token=" . urlencode($token);
            $fullname = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
            sendRegistrationVerificationEmail($user['email'], $fullname, $user['role'], $verificationLink);

            $login_error = 'Your email is not verified yet. We sent a new verification email to your registered email address.';
        } elseif($user['role'] !== 'superadmin' && $user['account_status'] !== 'approved'){
            $login_error = 'Account not approved by admin yet.';
        } else {
            $failedAttempts = intval($user['failed_login_attempts']);
            $otpRequiredUntil = $user['require_otp_until'];
            $requiresOtp = $failedAttempts >= 3
                || (!empty($otpRequiredUntil) && strtotime($otpRequiredUntil) > time());

            if($requiresOtp){
                $otp = (string) random_int(100000, 999999);
                $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
                $userId = intval($user['user_id']);

                db_execute(
                    $conn,
                    "UPDATE user_auth
                     SET otp_code=?,
                         otp_expiry=?,
                         require_otp_until=DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                     WHERE user_id=?",
                    'ssi',
                    [$otp, $expiry, $userId]
                );

                $_SESSION['temp_user'] = $userId;
                sendOTP($email, $otp);

                echo "<script>
                alert('Multiple failed login attempts were detected. An OTP was sent to your email to verify that it is really you.');
                window.location='verify_otp.php';
                </script>";
                exit();
            }

            $userId = intval($user['user_id']);
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = $user['role'];
            unset($_SESSION['temp_user']);

            db_execute(
                $conn,
                "UPDATE user_auth
                 SET failed_login_attempts=0,
                     require_otp_until=NULL,
                     otp_code=NULL,
                     otp_expiry=NULL
                 WHERE user_id=?",
                'i',
                [$userId]
            );

            db_execute(
                $conn,
                "INSERT INTO logs (user_id, action)
                 VALUES (?, ?)",
                'is',
                [$userId, 'Logged in successfully']
            );

            header("Location: " . dashboardPathForRole($user['role']));
            exit();
        }
    } else {
        unset($_SESSION['temp_user']);
        $login_error = 'Invalid password. Please try again.';

        if($user){
            $userId = intval($user['user_id']);
            $newFailedAttempts = intval($user['failed_login_attempts']) + 1;

            db_execute(
                $conn,
                "UPDATE user_auth
                 SET failed_login_attempts=failed_login_attempts + 1,
                     require_otp_until=CASE
                         WHEN failed_login_attempts + 1 >= 3 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                         ELSE require_otp_until
                     END
                 WHERE user_id=?",
                'i',
                [$userId]
            );

            if($newFailedAttempts >= 3){
                if(intval($user['email_verified']) !== 1){
                    $login_error = 'Please verify your email first before logging in.';
                } elseif($user['role'] !== 'superadmin' && $user['account_status'] !== 'approved'){
                    $login_error = 'Your account is still waiting for admin approval.';
                } else {
                    $otp = (string) random_int(100000, 999999);
                    $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

                    db_execute(
                        $conn,
                        "UPDATE user_auth
                         SET otp_code=?,
                             otp_expiry=?,
                             require_otp_until=DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                         WHERE user_id=?",
                        'ssi',
                        [$otp, $expiry, $userId]
                    );

                    $_SESSION['temp_user'] = $userId;
                    sendOTP($user['email'], $otp);

                    echo "<script>
                    alert('Multiple failed login attempts detected. An OTP was sent to your email.');
                    window.location='verify_otp.php';
                    </script>";
                    exit();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="container">
    <h2>Login</h2>

    <?php if($login_error !== ''): ?>
        <p style="color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>

    <div class="link">
        <a href="resend_verification.php">Resend Verification</a>
    </div>

    <div class="link">
        <a href="forgot_password.php">Forgot Password?</a>
    </div>

    <div class="link">
        <a href="register.php">Create an Account</a>
    </div>
</div>

</body>
</html>
