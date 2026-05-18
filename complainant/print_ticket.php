<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'complainant'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/paper_pdf.php');

$user_id = intval($_SESSION['user_id']);
$complaint_id = intval($_GET['id'] ?? 0);

$complaint = $complaint_id > 0
    ? db_select_one($conn,
    "SELECT complaints.complaint_id,
            complaints.tracking_number,
            complaints.subject,
            complaints.description,
            complaints.status,
            complaints.created_at,
            complainant.firstname AS complainant_firstname,
            complainant.lastname AS complainant_lastname,
            complainant.email AS complainant_email,
            staff.firstname AS staff_firstname,
            staff.lastname AS staff_lastname
     FROM complaints
     INNER JOIN users complainant ON complaints.complainant_id = complainant.user_id
     LEFT JOIN users staff ON complaints.assigned_staff_id = staff.user_id
     WHERE complaints.complaint_id=?
     AND complaints.complainant_id=?
     LIMIT 1",
     'ii',
     [$complaint_id, $user_id])
    : null;

if(!$complaint){
    http_response_code(404);
    echo 'Complaint copy not found.';
    exit();
}

$complainantName = trim($complaint['complainant_firstname'] . ' ' . $complaint['complainant_lastname']);
$staffName = trim(($complaint['staff_firstname'] ?? '') . ' ' . ($complaint['staff_lastname'] ?? ''));

$pdf = new SimplePdf();
paper_pdf_header($pdf, 'COMPLAINT ACKNOWLEDGEMENT COPY');
$pdf->labelValue('Tracking No.', $complaint['tracking_number']);
$pdf->labelValue('Complaint ID', (string)intval($complaint['complaint_id']));
$pdf->labelValue('Date Submitted', date('F j, Y g:i A', strtotime($complaint['created_at'])));
$pdf->labelValue('Current Status', $complaint['status']);
$pdf->blank();

$pdf->line('I. COMPLAINANT INFORMATION');
$pdf->blank(8);
$pdf->labelValue('Full Name', $complainantName);
$pdf->labelValue('Email', $complaint['complainant_email']);
$pdf->labelValue('Assigned Staff', $staffName !== '' ? $staffName : 'Not assigned yet');
$pdf->blank();

$pdf->line('II. COMPLAINT INFORMATION');
$pdf->blank(8);
$pdf->labelValue('Subject', $complaint['subject']);
$pdf->line('Description:');
$pdf->paragraph($complaint['description']);
$pdf->blank();

$pdf->line('III. REMINDER');
$pdf->blank(8);
$pdf->paragraph('Keep this complaint copy for follow-up. Use the tracking number when checking the status of your complaint.');
$pdf->blank();

$pdf->line('IV. SIGNATURES');
$pdf->blank(8);
paper_pdf_signature($pdf, 'Received By', 'Barangay Digital Complaint Desk System');
paper_pdf_signature($pdf, 'Complainant', $complainantName);

paper_pdf_stream($pdf, 'Complaint Copy - ' . $complaint['tracking_number'] . '.pdf');
?>
