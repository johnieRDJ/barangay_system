<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

function sendResidencySchedule($email, $fullname, $schedule){

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
$mail->Subject = 'Barangay Residency Appointment Schedule';

$mail->Body = "
<h3>Barangay Residency Appointment</h3>

<p>Hello <b>$fullname</b>,</p>

<p>Your account requires residency verification.</p>

<p>Your appointment schedule is:</p>

<h2>$schedule</h2>

<p>Please visit the Barangay Office at the scheduled time and bring a valid ID.</p>

<p>Thank you.</p>

";

$mail->send();

}catch(Exception $e){

echo "Mailer Error: " . $mail->ErrorInfo;

}

}
?>