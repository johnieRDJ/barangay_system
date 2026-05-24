<?php
require_once __DIR__ . '/mailer.php';

if(!function_exists('sendResidencySchedule')){
function sendResidencySchedule($email, $fullname, $schedule): bool
{

$mail = createBarangayMailer();

try{

$mail->addAddress($email);
$mail->Subject = 'Barangay Residency Appointment Schedule';

$mail->Body = "
<h3>Barangay Residency Appointment</h3>

<p>Hello <b>$fullname</b>,</p>

<p>Your account requires residency verification.</p>

<p>Your appointment schedule is:</p>

<h2>$schedule</h2>

<p>Please visit the Barangay Office at the scheduled time and bring a valid ID.</p>

<p>You may log in here: <a href=\"" . APP_URL . "/auth/login.php\">Barangay Complaint System</a></p>

<p>Thank you.</p>

";

return $mail->send();

}catch(Throwable $e){

error_log("Residency schedule mailer error: " . $mail->ErrorInfo . " " . $e->getMessage());
return false;

}

}
}
?>
