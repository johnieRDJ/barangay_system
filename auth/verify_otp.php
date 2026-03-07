<?php include('../config/database.php'); session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="container">
    <h2>Verify OTP</h2>

    <form method="POST">
        <input type="text" name="otp" placeholder="Enter 6-digit OTP" required>
        <button type="submit" name="verify">Verify</button>
    </form>
</div>

</body>
</html>

<?php
if(isset($_POST['verify'])){

    $entered_otp = $_POST['otp'];
    $user_id = $_SESSION['temp_user'];

    $query = mysqli_query($conn, 
        "SELECT * FROM users WHERE user_id='$user_id'");
    $user = mysqli_fetch_assoc($query);

    if($entered_otp == $user['otp_code'] 
    && strtotime($user['otp_expiry']) > time()){

    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = $user['role'];
    unset($_SESSION['temp_user']);

    // Add log
    mysqli_query($conn, 
    "INSERT INTO logs (user_id, action) 
     VALUES ('$user_id','Logged in successfully with 2FA')");

    // ROLE-BASED REDIRECTION
    if($user['role'] == 'admin'){
        header("Location: ../admin/dashboard.php");
    }
    elseif($user['role'] == 'staff'){
        header("Location: ../staff/dashboard.php");
    }
    else{
        header("Location: ../complainant/dashboard.php");
    }

    } else {
        echo "<script>alert('Invalid or expired OTP');</script>";
    }
}
?>