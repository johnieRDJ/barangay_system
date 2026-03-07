<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

function sendAccountStatus($email, $fullname, $status){

$mail = new PHPMailer(true);

try{

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;

$mail->Username = 'argierydertz@gmail.com';
$mail->Password = 'xygl mvhd jfpv sjjx';

$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('argierydertz@gmail.com', 'Barangay Digital Complaint System');

$mail->addAddress($email);

$mail->isHTML(true);

if($status == "approved"){

$mail->Subject = 'Your Account Has Been Approved';

$mail->Body = "
<h3>Account Approved</h3>

<p>Hello <b>$fullname</b>,</p>

<p>Your account has been <b>approved</b> by the Barangay Administrator.</p>

<p>You can now login to the system and submit complaints.</p>

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

}catch(Exception $e){

echo "Mailer Error: " . $mail->ErrorInfo;

}

}
?>
