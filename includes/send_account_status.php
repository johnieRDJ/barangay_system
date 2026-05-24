<?php
require_once __DIR__ . '/mailer.php';

function sendAccountStatus($email, $fullname, $status){

$mail = createBarangayMailer();

try{

$mail->addAddress($email);

if($status == "approved"){

$mail->Subject = 'Your Account Has Been Approved';

$mail->Body = "
<h3>Account Approved</h3>

<p>Hello <b>$fullname</b>,</p>

<p>Your account has been <b>approved</b> by the Barangay Administrator.</p>

<p>You can now login to the system.</p>

<p>Please complete your <b>My Profile</b> information after logging in, including your address, phone number, age, civil status, valid ID, and profile picture. This helps the Barangay review and process your requests properly.</p>

<p><a href='" . rtrim(defined('APP_URL') ? APP_URL : 'http://localhost/barangay', '/') . "/auth/login.php'>Login to your account</a></p>

<p>Thank you.</p>
";

}else{

$mail->Subject = 'Your Account Registration Was Rejected';

$mail->Body = "
<h3>Account Rejected</h3>

<p>Hello <b>$fullname</b>,</p>

<p>Unfortunately your account registration has been <b>rejected</b>.</p>

<p>Please visit the Barangay Office if you believe this is a mistake.</p>

<p>Thank you.</p>
";

}

$mail->send();

}catch(Throwable $e){

echo "Mailer Error: " . $mail->ErrorInfo;

}

}
?>
