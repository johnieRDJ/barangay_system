<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

function sendOTP($email, $otp){

    $mail = new PHPMailer(true);

    try{

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // YOUR EMAIL
        $mail->Username   = 'argierydertz@gmail.com';

        // APP PASSWORD
        $mail->Password   = 'xygl mvhd jfpv sjjx';

        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('argierydertz@gmail.com', 'Barangay Digital Complaint System');

        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';

        $mail->Body = "
        <h3>Your OTP Code</h3>
        <p>Your verification code is:</p>
        <h2>$otp</h2>
        <p>This code will expire in 5 minutes.</p>
        ";

        $mail->send();

    } catch (Exception $e){
        echo "Mailer Error: " . $mail->ErrorInfo;
    }

}
?>