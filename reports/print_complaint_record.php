<?php
session_start();

if(
    !isset($_SESSION['user_id'], $_SESSION['role']) ||
    !in_array($_SESSION['role'], ['admin', 'staff'], true)
){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/paper_pdf.php');

$user_id = intval($_SESSION['user_id']);
$role = $_SESSION['role'];
$complaint_id = intval($_GET['id'] ?? 0);
$types = 'i';
$params = [$complaint_id];
$accessCondition = "";

if($role === 'staff'){
    $accessCondition = "AND complaints.assigned_staff_id=?";
    $types .= 'i';
    $params[] = $user_id;
}

$complaint = $complaint_id > 0
    ? db_select_one($conn,
    "SELECT complaints.complaint_id,
            complaints.tracking_number,
            complaints.subject,
            complaints.description,
            complaints.status,
            complaints.staff_comment,
            complaints.created_at,
            complainant.firstname AS complainant_firstname,
            complainant.lastname AS complainant_lastname,
            complainant.email AS complainant_email,
            complainant_profile.address AS complainant_address,
            complainant_profile.purok AS complainant_purok,
            complainant_profile.phone AS complainant_phone,
            staff.firstname AS staff_firstname,
            staff.lastname AS staff_lastname,
            staff.email AS staff_email
     FROM complaints
     INNER JOIN users complainant ON complaints.complainant_id = complainant.user_id
     LEFT JOIN user_profiles complainant_profile ON complainant.user_id = complainant_profile.user_id
     LEFT JOIN users staff ON complaints.assigned_staff_id = staff.user_id
     WHERE complaints.complaint_id=?
     $accessCondition
     LIMIT 1",
     $types,
     $params)
    : null;

if(!$complaint){
    http_response_code(404);
    echo 'Complaint record not found or you do not have access to it.';
    exit();
}

$timeline = db_select_all($conn,
"SELECT complaint_updates.*,
        users.firstname,
        users.lastname
 FROM complaint_updates
 LEFT JOIN users ON complaint_updates.actor_user_id = users.user_id
 WHERE complaint_updates.complaint_id=?
 ORDER BY complaint_updates.created_at ASC, complaint_updates.update_id ASC",
 'i',
 [$complaint_id]);

$preparedBy = db_select_one($conn,
"SELECT users.firstname,
        users.lastname,
        user_profiles.signature_image
 FROM users
 LEFT JOIN user_profiles ON users.user_id = user_profiles.user_id
 WHERE users.user_id=? LIMIT 1",
 'i',
 [$user_id]);

$preparedByName = $preparedBy ? trim($preparedBy['firstname'] . ' ' . $preparedBy['lastname']) : ucfirst($role);
$preparedSignature = paper_pdf_jpeg_path(!empty($preparedBy['signature_image']) ? 'uploads/signatures/' . $preparedBy['signature_image'] : null, true);
$complainantName = trim($complaint['complainant_firstname'] . ' ' . $complaint['complainant_lastname']);
$staffName = trim(($complaint['staff_firstname'] ?? '') . ' ' . ($complaint['staff_lastname'] ?? ''));

$pdf = new SimplePdf();
paper_pdf_header($pdf, 'OFFICIAL COMPLAINT RECORD FORM');
$pdf->labelValue('Tracking No.', $complaint['tracking_number']);
$pdf->labelValue('Complaint ID', (string)intval($complaint['complaint_id']));
$pdf->labelValue('Date Submitted', date('F j, Y g:i A', strtotime($complaint['created_at'])));
$pdf->labelValue('Current Status', $complaint['status']);
$pdf->labelValue('Generated On', date('F j, Y g:i A'));
$pdf->blank();

$pdf->line('I. COMPLAINANT INFORMATION');
$pdf->blank(8);
$pdf->labelValue('Full Name', $complainantName);
$pdf->labelValue('Email', $complaint['complainant_email']);
$pdf->labelValue('Phone', $complaint['complainant_phone'] ?: 'N/A');
$pdf->labelValue('Address', $complaint['complainant_address'] ?: 'N/A');
$pdf->labelValue('Purok', !empty($complaint['complainant_purok']) ? 'Purok ' . $complaint['complainant_purok'] : 'N/A');
$pdf->blank();

$pdf->line('II. COMPLAINT DETAILS');
$pdf->blank(8);
$pdf->labelValue('Subject', $complaint['subject']);
$pdf->line('Description:');
$pdf->paragraph($complaint['description']);
$pdf->blank(4);
$pdf->line('Latest Staff Remarks:');
$pdf->paragraph(!empty($complaint['staff_comment']) ? $complaint['staff_comment'] : 'No staff remarks yet.');
$pdf->blank();

$pdf->line('III. ASSIGNMENT');
$pdf->blank(8);
$pdf->labelValue('Assigned Staff', $staffName !== '' ? $staffName : 'Not assigned yet');
$pdf->labelValue('Staff Email', !empty($complaint['staff_email']) ? $complaint['staff_email'] : 'N/A');
$pdf->blank();

$pdf->line('IV. PROGRESS TIMELINE');
$pdf->blank(12);
if(empty($timeline)){
    $pdf->line('No timeline updates recorded yet.');
} else {
    foreach($timeline as $update){
        $actorName = trim(($update['firstname'] ?? '') . ' ' . ($update['lastname'] ?? ''));
        $actorLabel = $actorName !== '' ? $actorName : ucfirst($update['actor_role']);
        $pdf->line(date('F j, Y g:i A', strtotime($update['created_at'])) . ' - ' . $update['status_snapshot']);
        $pdf->paragraph('Updated by ' . $actorLabel . ': ' . $update['message']);
        $pdf->blank(12);
    }
}

$pdf->addPage();
$pdf->line('V. SIGNATURES');
$pdf->blank(8);
paper_pdf_signature($pdf, 'Prepared By', $preparedByName, $preparedSignature);
paper_pdf_signature($pdf, 'Reviewed / Approved By', 'Punong Barangay');

db_execute($conn,
"INSERT INTO logs (user_id, action)
 VALUES (?, ?)",
 'is',
 [$user_id, "Generated printable complaint record for complaint ID $complaint_id"]);

paper_pdf_stream($pdf, 'Complaint Record - ' . $complaint['tracking_number'] . '.pdf');
?>
