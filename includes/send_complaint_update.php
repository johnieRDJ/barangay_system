<?php
require_once __DIR__ . '/mailer.php';

function createComplaintMailer(){
    return createBarangayMailer();
}

function sendComplaintTimelineUpdate(
    string $email,
    string $fullname,
    string $subject,
    string $trackingNumber,
    string $status,
    string $message,
    string $updatedBy,
    ?string $buttonUrl = null
): bool
{
    $mail = createComplaintMailer();
    $safeFullname = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $safeTrackingNumber = htmlspecialchars($trackingNumber, ENT_QUOTES, 'UTF-8');
    $safeStatus = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    $safeUpdatedBy = htmlspecialchars($updatedBy, ENT_QUOTES, 'UTF-8');
    $appUrl = rtrim(defined('APP_URL') ? APP_URL : 'http://localhost/barangay', '/');
    $complaintsUrl = $buttonUrl ?: $appUrl . '/complainant/my_complaints.php';
    $loginUrl = $appUrl . '/auth/login.php';
    $safeComplaintsUrl = htmlspecialchars($complaintsUrl, ENT_QUOTES, 'UTF-8');
    $safeLoginUrl = htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8');

    try{
        $mail->addAddress($email);
        $mail->Subject = "Complaint Update: $trackingNumber";
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;'>
                <h2 style='margin-bottom: 8px;'>Complaint Timeline Update</h2>
                <p>Hello <strong>$safeFullname</strong>,</p>
                <p>A new update was added to this complaint:</p>

                <div style='background: #f8fafc; border: 1px solid #dbe3ea; border-radius: 10px; padding: 16px; margin: 18px 0;'>
                    <p style='margin: 0 0 8px;'><strong>Tracking Number:</strong> $safeTrackingNumber</p>
                    <p style='margin: 0 0 8px;'><strong>Subject:</strong> $safeSubject</p>
                    <p style='margin: 0 0 8px;'><strong>Status:</strong> $safeStatus</p>
                    <p style='margin: 0;'><strong>Updated By:</strong> $safeUpdatedBy</p>
                </div>

                <p style='margin-bottom: 6px;'><strong>Update message:</strong></p>
                <div style='background: #ffffff; border-left: 4px solid #1d4f91; padding: 12px 14px; margin-bottom: 20px;'>
                    $safeMessage
                </div>

                <p style='margin-bottom: 20px;'>
                    <a href='$safeComplaintsUrl' style='display: inline-block; background: #1d4f91; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600;'>
                        View Complaint Timeline
                    </a>
                </p>

                <p style='margin-bottom: 8px;'>If the button does not work, copy and paste this link into your browser:</p>
                <p style='word-break: break-all; margin-top: 0;'><a href='$safeComplaintsUrl'>$safeComplaintsUrl</a></p>

                <p style='margin-bottom: 8px;'>Login here if the page asks you to sign in:</p>
                <p style='word-break: break-all; margin-top: 0;'><a href='$safeLoginUrl'>$safeLoginUrl</a></p>
            </div>
        ";

        $mail->AltBody = "Hello $fullname,\n\nA new update was added to this complaint.\n\nTracking Number: $trackingNumber\nSubject: $subject\nStatus: $status\nUpdated By: $updatedBy\n\nUpdate message:\n$message\n\nView the timeline here:\n$complaintsUrl\n\nLogin here if needed:\n$loginUrl";
        $mail->send();

        return true;
    } catch(Throwable $e){
        return false;
    }
}

function sendComplaintUpdate($email, $fullname, $subject, $status){
    return sendComplaintTimelineUpdate(
        $email,
        $fullname,
        $subject,
        'Complaint',
        $status,
        "Status has been updated to $status.",
        'Barangay Staff'
    );
}
?>
