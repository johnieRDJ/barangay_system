<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/complaint_updates.php');
include('../includes/send_complaint_update.php');
include('../includes/simple_pdf.php');
include('../includes/blotter_pdf.php');

$userId = intval($_SESSION['user_id']);
$complaintId = intval($_POST['complaint_id'] ?? 0);
$page = max(1, intval($_POST['page'] ?? 1));
$perPage = intval($_POST['per_page'] ?? 10);
$perPage = in_array($perPage, [10, 20, 30, 40, 50], true) ? $perPage : 10;
$statusFilter = $_POST['status_filter'] ?? '';
$statusFilter = in_array($statusFilter, ['Pending', 'In Progress', 'Reopened', 'Awaiting Confirmation', 'Resolved'], true) ? $statusFilter : '';
$redirectParams = [
    'blotter' => 1,
    'page' => $page,
    'per_page' => $perPage,
];

if($statusFilter !== ''){
    $redirectParams['status'] = $statusFilter;
}

function post_value(string $key): string
{
    return trim($_POST[$key] ?? '');
}

function display_date(string $value): string
{
    if($value === ''){
        return '';
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('F j, Y', $timestamp) : $value;
}

function display_time(string $value): string
{
    if($value === ''){
        return '';
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('g:i A', $timestamp) : $value;
}

function pdf_jpeg_path(?string $relativePath): ?string
{
    if(!$relativePath){
        return null;
    }

    $path = realpath(__DIR__ . '/../' . ltrim($relativePath, '/\\'));
    $extension = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : '';

    return $path && in_array($extension, ['jpg', 'jpeg'], true) ? $path : null;
}

function pdf_signature_block(SimplePdf $pdf, string $label, string $name, string $date = '', ?string $signaturePath = null): void
{
    $pdf->line($label . ':');
    $signatureLineY = $pdf->getY();

    if($signaturePath){
        $pdf->image($signaturePath, 300, max(72, $signatureLineY + 2), 46, 18);
    }

    $pdf->labelValue('Signature', '');
    $pdf->labelValue('Name', $name);
    $pdf->labelValue('Date', $date);
    $pdf->blank(6);
}

if($complaintId <= 0){
    header("Location: view_complaints.php?" . http_build_query($redirectParams));
    exit();
}

$complaint = db_select_one($conn,
"SELECT complaints.*,
        complainant.firstname AS complainant_firstname,
        complainant.lastname AS complainant_lastname,
        complainant.email AS complainant_email,
        complainant_profile.address AS complainant_address,
        complainant_profile.phone AS complainant_phone,
        complainant_profile.age AS complainant_age,
        complainant_profile.gender AS complainant_gender,
        complainant_profile.civil_status AS complainant_civil_status
 FROM complaints
 INNER JOIN users complainant ON complaints.complainant_id = complainant.user_id
 LEFT JOIN user_profiles complainant_profile ON complainant.user_id = complainant_profile.user_id
 WHERE complaints.complaint_id=?
 AND complaints.assigned_staff_id=?
 LIMIT 1",
 'ii',
 [$complaintId, $userId]);

if(!$complaint){
    header("Location: view_complaints.php?" . http_build_query($redirectParams));
    exit();
}

$recentReport = db_select_one($conn,
"SELECT report_id
 FROM blotter_reports
 WHERE complaint_id=?
 AND staff_user_id=?
 AND created_at >= (NOW() - INTERVAL 30 SECOND)
 ORDER BY report_id DESC
 LIMIT 1",
 'ii',
 [$complaintId, $userId]);

if($recentReport){
    header("Location: view_complaints.php?" . http_build_query($redirectParams));
    exit();
}

$staff = db_select_one($conn,
"SELECT users.firstname,
        users.lastname,
        user_profiles.signature_image
 FROM users
 LEFT JOIN user_profiles ON users.user_id = user_profiles.user_id
 WHERE users.user_id=? LIMIT 1",
'i',
[$userId]);
$staffName = $staff ? trim($staff['firstname'] . ' ' . $staff['lastname']) : 'Barangay Staff';
$staffSignature = $staff['signature_image'] ?? null;
$complainantName = post_value('complainant_name') ?: trim($complaint['complainant_firstname'] . ' ' . $complaint['complainant_lastname']);
$respondentName = post_value('respondent_name');
$blotterNo = post_value('blotter_no') ?: ($complaint['tracking_number'] ?: 'CMP-' . $complaintId);
$dateFiled = display_date(post_value('date_filed')) ?: date('F j, Y');
$timeFiled = display_time(post_value('time_filed')) ?: date('g:i A');
$barangay = post_value('barangay');
$city = post_value('city');
$province = post_value('province');

$complaintTypes = $_POST['complaint_types'] ?? [];
if(!is_array($complaintTypes)){
    $complaintTypes = [];
}
$complaintTypeOther = post_value('complaint_type_other');
$requestedActions = $_POST['requested_actions'] ?? [];
if(!is_array($requestedActions)){
    $requestedActions = [];
}
$otherAction = post_value('other_action');
$reportData = [
    'province' => $province,
    'city' => $city,
    'barangay' => $barangay,
    'blotter_no' => $blotterNo,
    'date_filed' => $dateFiled,
    'time_filed' => $timeFiled,
    'complainant_name' => $complainantName,
    'complainant_age' => post_value('complainant_age') ?: (string)($complaint['complainant_age'] ?? ''),
    'complainant_gender' => post_value('complainant_gender') ?: ($complaint['complainant_gender'] ?? ''),
    'complainant_civil_status' => post_value('complainant_civil_status') ?: ($complaint['complainant_civil_status'] ?? ''),
    'complainant_address' => post_value('complainant_address') ?: ($complaint['complainant_address'] ?? ''),
    'complainant_contact' => post_value('complainant_contact') ?: ($complaint['complainant_phone'] ?? ''),
    'respondent_name' => $respondentName,
    'respondent_age' => post_value('respondent_age'),
    'respondent_gender' => post_value('respondent_gender'),
    'respondent_civil_status' => post_value('respondent_civil_status'),
    'respondent_address' => post_value('respondent_address'),
    'respondent_contact' => post_value('respondent_contact'),
    'incident_date' => display_date(post_value('incident_date')),
    'incident_time' => display_time(post_value('incident_time')),
    'incident_place' => post_value('incident_place'),
    'complaint_types' => $complaintTypes,
    'complaint_type_other' => $complaintTypeOther,
    'statement_details' => post_value('statement_details') ?: $complaint['description'],
    'requested_actions' => $requestedActions,
    'other_action' => $otherAction,
    'witness_name' => post_value('witness_name'),
    'witness_address' => post_value('witness_address'),
    'witness_contact' => post_value('witness_contact'),
    'witness_statement' => post_value('witness_statement'),
    'action_date' => display_date(post_value('action_date')),
    'action_remarks' => post_value('action_remarks'),
    'recorded_by' => post_value('recorded_by') ?: $staffName,
    'recorded_position' => post_value('recorded_position') ?: 'Barangay Secretary / Desk Officer',
    'issued_day' => post_value('issued_day'),
    'issued_month' => post_value('issued_month'),
    'issued_year_suffix' => post_value('issued_year_suffix'),
    'prepared_by' => post_value('prepared_by') ?: 'Barangay Secretary / Desk Officer',
    'approved_by' => post_value('approved_by') ?: 'Punong Barangay',
];

$pdf = new SimplePdf();
$citySeal = pdf_jpeg_path('uploads/system/tangub_off_seal.jpg');
$provinceSeal = pdf_jpeg_path('uploads/system/mis_occ_official_seal.jpg');

if($citySeal){
    $pdf->image($citySeal, 118, 672, 72);
}

if($provinceSeal){
    $pdf->image($provinceSeal, 434, 678, 58);
}

$pdf->setY(710);
$pdf->setFontSize(11);
$pdf->center('Republic of the Philippines');
$pdf->center('Province of ' . ($province ?: '____________________'));
$pdf->center('City/Municipality of ' . ($city ?: '____________________'));
$pdf->center('Barangay ' . ($barangay ?: '____________________'));
$pdf->center('Office of the Punong Barangay');
$pdf->blank(12);
$pdf->setFontSize(12);
$pdf->line('BARANGAY BLOTTER / COMPLAINT REPORT');
$pdf->blank(12);
$pdf->labelValue('Blotter No.', $blotterNo);
$pdf->labelValue('Date Filed', $dateFiled);
$pdf->labelValue('Time Filed', $timeFiled);
$pdf->blank();

$pdf->line('I. COMPLAINANT INFORMATION');
$pdf->blank(8);
$pdf->labelValue('Full Name', $complainantName);
$pdf->labelValue('Age', post_value('complainant_age') ?: (string)($complaint['complainant_age'] ?? ''));
$pdf->labelValue('Gender', post_value('complainant_gender') ?: ($complaint['complainant_gender'] ?? ''));
$pdf->labelValue('Civil Status', post_value('complainant_civil_status') ?: ($complaint['complainant_civil_status'] ?? ''));
$pdf->labelValue('Address', post_value('complainant_address') ?: ($complaint['complainant_address'] ?? ''));
$pdf->labelValue('Contact Number', post_value('complainant_contact') ?: ($complaint['complainant_phone'] ?? ''));
$pdf->blank();

$pdf->line('II. PERSON COMPLAINED AGAINST');
$pdf->blank(8);
$pdf->labelValue('Full Name', $respondentName);
$pdf->labelValue('Age', post_value('respondent_age'));
$pdf->labelValue('Gender', post_value('respondent_gender'));
$pdf->labelValue('Civil Status', post_value('respondent_civil_status'));
$pdf->labelValue('Address', post_value('respondent_address'));
$pdf->labelValue('Contact Number', post_value('respondent_contact'));
$pdf->blank();

$pdf->line('III. INCIDENT DETAILS');
$pdf->blank(8);
$pdf->labelValue('Date of Incident', display_date(post_value('incident_date')));
$pdf->labelValue('Time of Incident', display_time(post_value('incident_time')));
$pdf->labelValue('Place of Incident', post_value('incident_place'));
$pdf->line('Type of Complaint:');
$hasOtherComplaintType = in_array('Other', $complaintTypes, true) || $complaintTypeOther !== '';
foreach(['Neighborhood Conflict', 'Minor Property Damage', 'Theft', 'Threat or Harassment', 'Physical/Verbal Dispute'] as $type){
    $pdf->line((in_array($type, $complaintTypes, true) ? '[x] ' : '[ ] ') . $type);
}
$pdf->line(($hasOtherComplaintType ? '[x] ' : '[ ] ') . 'Other: ' . $complaintTypeOther);
$pdf->blank();

$pdf->addPage();
$pdf->line('IV. STATEMENT OF COMPLAINT');
$pdf->blank(8);
$pdf->paragraph('I, ' . ($complainantName ?: '____________________') . ', of legal age and a resident of ' . (post_value('complainant_address') ?: ($complaint['complainant_address'] ?? '____________________')) . ', respectfully file this complaint before the Barangay against ' . ($respondentName ?: '____________________') . '.');
$pdf->blank(4);
$pdf->paragraph('On ' . (display_date(post_value('incident_date')) ?: '____________________') . ', at around ' . (display_time(post_value('incident_time')) ?: '____________________') . ', the incident happened at ' . (post_value('incident_place') ?: '____________________') . '.');
$pdf->blank(4);
$pdf->line('The details of the complaint are as follows:');
$pdf->paragraph(post_value('statement_details') ?: $complaint['description']);
$pdf->blank(4);
$pdf->paragraph('Because of this incident, I am requesting the assistance of the Barangay to properly record this matter in the barangay blotter and to take the necessary action according to barangay rules and procedures.');
$pdf->blank();

$pdf->line('V. REQUESTED ACTION');
$pdf->blank(8);
foreach([
    'Record this incident in the barangay blotter',
    'Summon the respondent for mediation',
    'Assist both parties in settling the matter peacefully',
    'Issue a certification if needed',
] as $action){
    $pdf->line((in_array($action, $requestedActions, true) ? '[x] ' : '[ ] ') . $action);
}
$pdf->line((in_array('Other', $requestedActions, true) ? '[x] ' : '[ ] ') . 'Take other proper action: ' . $otherAction);
$pdf->blank();

$pdf->line('VI. WITNESS INFORMATION');
$pdf->blank(8);
$pdf->labelValue('Name of Witness', post_value('witness_name'));
$pdf->labelValue('Address', post_value('witness_address'));
$pdf->labelValue('Contact Number', post_value('witness_contact'));
$pdf->line('Statement of Witness:');
$pdf->paragraph(post_value('witness_statement'));
$pdf->blank();

$pdf->line('VII. ACTION TAKEN BY THE BARANGAY');
$pdf->blank(8);
$pdf->labelValue('Date of Action', display_date(post_value('action_date')));
$pdf->labelValue('Remarks', post_value('action_remarks'));
$pdf->blank();

$pdf->addPage();
$pdf->line('VIII. SIGNATURES');
$pdf->blank(8);
$staffSignaturePdfPath = pdf_jpeg_path(!empty($staffSignature) ? 'uploads/signatures/' . $staffSignature : null);
pdf_signature_block($pdf, 'Complainant', $complainantName);
pdf_signature_block($pdf, 'Received and Recorded By', post_value('recorded_by') ?: $staffName, $dateFiled, $staffSignaturePdfPath);
$pdf->labelValue('Position', post_value('recorded_position') ?: 'Barangay Secretary / Desk Officer');
pdf_signature_block($pdf, 'Approved By', post_value('approved_by') ?: 'Punong Barangay');
$pdf->blank();

$pdf->line('CERTIFICATION');
$pdf->paragraph('This is to certify that the above complaint was officially recorded in the Barangay Blotter of Barangay ' . ($barangay ?: '____________________') . ' on ' . ($dateFiled ?: '____________________') . ' at ' . ($timeFiled ?: '____________________') . '.');
$pdf->paragraph('Issued this ' . post_value('issued_day') . ' day of ' . post_value('issued_month') . ', 20' . post_value('issued_year_suffix') . ' at Barangay ' . ($barangay ?: '____________________') . ', City/Municipality of ' . ($city ?: '____________________') . '.');
$pdf->blank(14);
$pdf->line('Prepared by:');
$pdf->line(post_value('prepared_by') ?: 'Barangay Secretary / Desk Officer');
$pdf->blank(18);
$pdf->line('Approved by:');
$pdf->line(post_value('approved_by') ?: 'Punong Barangay');

$uploadsRoot = realpath(__DIR__ . '/../uploads');
$proofFolder = $uploadsRoot === false ? false : $uploadsRoot . DIRECTORY_SEPARATOR . 'complaint_proofs';

if($uploadsRoot === false || (!is_dir($proofFolder) && !mkdir($proofFolder, 0777, true))){
    header("Location: view_complaints.php?" . http_build_query($redirectParams));
    exit();
}

$storedFileName = 'blotter_' . $complaintId . '_' . $userId . '_' . time() . '.pdf';
$destinationPath = $proofFolder . DIRECTORY_SEPARATOR . $storedFileName;

if(!$pdf->output($destinationPath)){
    header("Location: view_complaints.php?" . http_build_query($redirectParams));
    exit();
}

$updateId = addComplaintUpdate(
    $conn,
    $complaintId,
    $userId,
    'staff',
    'blotter_report',
    $complaint['status'],
    'Barangay blotter / complaint report generated and attached.'
);

if($updateId){
    addComplaintUpdateAttachment(
        $conn,
        $updateId,
        'uploads/complaint_proofs/' . $storedFileName,
        'Barangay Blotter Report - ' . $blotterNo . '.pdf',
        'pdf',
        filesize($destinationPath)
    );

    if(!empty($staffSignature)){
        $staffSignaturePath = 'uploads/signatures/' . $staffSignature;
        $absoluteSignaturePath = realpath(__DIR__ . '/../' . $staffSignaturePath);

        if($absoluteSignaturePath !== false){
            addComplaintUpdateAttachment(
                $conn,
                $updateId,
                $staffSignaturePath,
                'Staff E-Signature - ' . $staffName,
                strtolower(pathinfo($staffSignaturePath, PATHINFO_EXTENSION)),
                filesize($absoluteSignaturePath)
            );
        }
    }
}

db_execute($conn,
"INSERT INTO blotter_reports (
    complaint_id,
    staff_user_id,
    complainant_user_id,
    status,
    report_path,
    report_original_name,
    report_data,
    staff_signature_image
)
VALUES (?, ?, ?, 'awaiting_complainant_signature', ?, ?, ?, ?)",
 'iiissss',
 [
    $complaintId,
    $userId,
    intval($complaint['complainant_id']),
    'uploads/complaint_proofs/' . $storedFileName,
    'Barangay Blotter Report - ' . $blotterNo . '.pdf',
    json_encode($reportData),
    $staffSignature
 ]);

db_execute($conn,
"INSERT INTO logs (user_id, action)
 VALUES (?, ?)",
 'is',
 [$userId, "Generated barangay blotter report for complaint ID $complaintId"]);

sendComplaintTimelineUpdate(
    $complaint['complainant_email'],
    $complainantName,
    $complaint['subject'],
    $complaint['tracking_number'],
    'Blotter Signature Needed',
    "A barangay blotter / complaint report has been generated for your complaint. Please open your complaint timeline and upload your scanned e-signature so the assigned staff can submit it to admin for approval.",
    $staffName,
    rtrim(defined('APP_URL') ? APP_URL : 'http://localhost/barangay', '/') . '/complainant/my_complaints.php'
);

header("Location: view_complaints.php?" . http_build_query($redirectParams));
exit();
?>
