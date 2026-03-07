<?php 
include('../config/database.php'); 
include('../includes/send_otp.php'); 
session_start(); 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="container">
    <h2>Login</h2>

    <form method="POST">
        
        <input type="email" name="email" placeholder="Email" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="login">Login</button>

    </form>

    <div class="link">
        <a href="register.php">Create an account</a>
    </div>
</div>

</body>
</html>

<?php
if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($query);

    if($user && password_verify($password, $user['password'])){

        if($user['account_status'] != 'approved'){
            echo "<script>alert('Account not approved by admin yet.');</script>";
            exit();
        }

        // Generate OTP
        $otp = rand(100000,999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        mysqli_query($conn, "UPDATE users 
        SET otp_code='$otp', otp_expiry='$expiry'
        WHERE user_id='".$user['user_id']."'");

        $_SESSION['temp_user'] = $user['user_id'];

        sendOTP($email, $otp);

        echo "<script>
        alert('OTP sent to your email.');
        window.location='verify_otp.php';
        </script>";

    } else {
        echo "<script>alert('Invalid email or password');</script>";
    }
}
?>