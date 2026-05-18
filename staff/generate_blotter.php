<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/complaint_updates.php');
include('../includes/simple_pdf.php');

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

$staff = db_select_one($conn,
"SELECT firstname, lastname FROM users WHERE user_id=? LIMIT 1",
'i',
[$userId]);
$staffName = $staff ? trim($staff['firstname'] . ' ' . $staff['lastname']) : 'Barangay Staff';
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

$pdf = new SimplePdf();
$pdf->setFontSize(12);
$pdf->center('Republic of the Philippines');
$pdf->center('Province of ' . ($province ?: '____________________'));
$pdf->center('City/Municipality of ' . ($city ?: '____________________'));
$pdf->center('Barangay ' . ($barangay ?: '____________________'));
$pdf->center('Office of the Punong Barangay');
$pdf->blank(12);
$pdf->center('[ Barangay Logo ]');
$pdf->blank(12);
$pdf->center('BARANGAY BLOTTER / COMPLAINT REPORT');
$pdf->blank(12);
$pdf->labelValue('Blotter No.', $blotterNo);
$pdf->labelValue('Date Filed', $dateFiled);
$pdf->labelValue('Time Filed', $timeFiled);
$pdf->blank();

$pdf->line('I. COMPLAINANT INFORMATION');
$pdf->labelValue('Full Name', $complainantName);
$pdf->labelValue('Age', post_value('complainant_age') ?: (string)($complaint['complainant_age'] ?? ''));
$pdf->labelValue('Gender', post_value('complainant_gender') ?: ($complaint['complainant_gender'] ?? ''));
$pdf->labelValue('Civil Status', post_value('complainant_civil_status') ?: ($complaint['complainant_civil_status'] ?? ''));
$pdf->labelValue('Address', post_value('complainant_address') ?: ($complaint['complainant_address'] ?? ''));
$pdf->labelValue('Contact Number', post_value('complainant_contact') ?: ($complaint['complainant_phone'] ?? ''));
$pdf->blank();

$pdf->line('II. PERSON COMPLAINED AGAINST');
$pdf->labelValue('Full Name', $respondentName);
$pdf->labelValue('Age', post_value('respondent_age'));
$pdf->labelValue('Gender', post_value('respondent_gender'));
$pdf->labelValue('Civil Status', post_value('respondent_civil_status'));
$pdf->labelValue('Address', post_value('respondent_address'));
$pdf->labelValue('Contact Number', post_value('respondent_contact'));
$pdf->blank();

$pdf->line('III. INCIDENT DETAILS');
$pdf->labelValue('Date of Incident', display_date(post_value('incident_date')));
$pdf->labelValue('Time of Incident', display_time(post_value('incident_time')));
$pdf->labelValue('Place of Incident', post_value('incident_place'));
$pdf->line('Type of Complaint:');
foreach(['Neighborhood Conflict', 'Minor Property Damage', 'Theft', 'Threat or Harassment', 'Physical/Verbal Dispute'] as $type){
    $pdf->line((in_array($type, $complaintTypes, true) ? '[x] ' : '[ ] ') . $type);
}
$pdf->line((in_array('Other', $complaintTypes, true) ? '[x] ' : '[ ] ') . 'Other: ' . $complaintTypeOther);
$pdf->blank();

$pdf->line('IV. STATEMENT OF COMPLAINT');
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
$pdf->labelValue('Name of Witness', post_value('witness_name'));
$pdf->labelValue('Address', post_value('witness_address'));
$pdf->labelValue('Contact Number', post_value('witness_contact'));
$pdf->line('Statement of Witness:');
$pdf->paragraph(post_value('witness_statement'));
$pdf->blank();

$pdf->line('VII. ACTION TAKEN BY THE BARANGAY');
$pdf->labelValue('Date of Action', display_date(post_value('action_date')));
$pdf->labelValue('Remarks', post_value('action_remarks'));
$pdf->blank();

$pdf->line('VIII. SIGNATURES');
$pdf->line('Complainant:');
$pdf->labelValue('Signature', '');
$pdf->labelValue('Name', $complainantName);
$pdf->labelValue('Date', '');
$pdf->blank(4);
$pdf->line('Respondent:');
$pdf->labelValue('Signature', '');
$pdf->labelValue('Name', $respondentName);
$pdf->labelValue('Date', '');
$pdf->blank(4);
$pdf->line('Witness:');
$pdf->labelValue('Signature', '');
$pdf->labelValue('Name', post_value('witness_name'));
$pdf->labelValue('Date', '');
$pdf->blank(4);
$pdf->line('Received and Recorded By:');
$pdf->labelValue('Signature', '');
$pdf->labelValue('Name', post_value('recorded_by') ?: $staffName);
$pdf->labelValue('Position', post_value('recorded_position') ?: 'Barangay Secretary / Desk Officer');
$pdf->labelValue('Date', $dateFiled);
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
}

db_execute($conn,
"INSERT INTO logs (user_id, action)
 VALUES (?, ?)",
 'is',
 [$userId, "Generated barangay blotter report for complaint ID $complaintId"]);

header("Location: view_complaints.php?" . http_build_query($redirectParams));
exit();
?>
