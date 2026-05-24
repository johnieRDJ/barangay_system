<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/complaint_updates.php');
include('../includes/send_complaint_update.php');
require_once __DIR__ . '/../includes/notifications.php';
include('../includes/validation.php');
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

function post_phone_value(string $key): string
{
    $tail = barangay_clean_phone($_POST[$key . '_tail'] ?? '');

    if($tail !== ''){
        return '09' . substr($tail, 0, 9);
    }

    return barangay_clean_phone(post_value($key));
}

function post_purok_value(string $key, string $fallback = ''): string
{
    $value = post_value($key) !== '' ? post_value($key) : $fallback;
    return in_array((string)$value, barangay_allowed_puroks(), true) ? (string)$value : '';
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

function pdf_jpeg_path(?string $relativePath, bool $cleanSignature = false): ?string
{
    if(!$relativePath){
        return null;
    }

    $path = realpath(__DIR__ . '/../' . ltrim($relativePath, '/\\'));
    $extension = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : '';

    if(!$path || !in_array($extension, ['jpg', 'jpeg', 'png'], true)){
        return null;
    }

    if($cleanSignature){
        $cleanPath = dirname($path) . DIRECTORY_SEPARATOR . pathinfo($path, PATHINFO_FILENAME) . '_pdf_clean.jpg';

        if(!is_file($cleanPath) || filemtime($cleanPath) < filemtime($path)){
            barangay_clean_signature_image_file($path, $cleanPath);
        }

        if(is_file($cleanPath)){
            return $cleanPath;
        }
    }

    return in_array($extension, ['jpg', 'jpeg'], true) ? $path : null;
}

function pdf_signature_block(SimplePdf $pdf, string $label, string $name, string $date = '', ?string $signaturePath = null): void
{
    $pdf->line($label . ':');
    $signatureLineY = $pdf->getY();
    $pdf->text('Signature:', 72, $signatureLineY);
    $pdf->horizontalLine(170, $signatureLineY - 3, 445);

    if($signaturePath){
        $pdf->image($signaturePath, 280, max(72, $signatureLineY - 0), 92, 32);
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
        complainant_profile.purok AS complainant_purok,
        complainant_profile.phone AS complainant_phone,
        complainant_profile.age AS complainant_age,
        complainant_profile.gender AS complainant_gender,
        complainant_profile.civil_status AS complainant_civil_status,
        complainant_profile.name_suffix AS complainant_name_suffix
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
$complainantSuffix = in_array(post_value('complainant_suffix'), barangay_allowed_suffixes(), true) ? post_value('complainant_suffix') : ($complaint['complainant_name_suffix'] ?? '');
$respondentSuffix = in_array(post_value('respondent_suffix'), barangay_allowed_suffixes(), true) ? post_value('respondent_suffix') : '';
$complainantName = barangay_clean_name(post_value('complainant_name')) ?: trim($complaint['complainant_firstname'] . ' ' . $complaint['complainant_lastname']);
$complainantName = trim($complainantName . ' ' . $complainantSuffix);
$respondentName = trim(barangay_clean_name(post_value('respondent_name')) . ' ' . $respondentSuffix);
$blotterNo = post_value('blotter_no') ?: ($complaint['tracking_number'] ?: 'CMP-' . $complaintId);
$dateFiled = display_date(post_value('date_filed')) ?: date('F j, Y');
$timeFiled = display_time(post_value('time_filed')) ?: date('g:i A');
$barangay = barangay_clean_location(post_value('barangay'));
$city = barangay_clean_location(post_value('city'));
$province = barangay_clean_location(post_value('province'));

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
    'complainant_gender' => in_array(post_value('complainant_gender'), barangay_allowed_genders(), true) ? post_value('complainant_gender') : ($complaint['complainant_gender'] ?? ''),
    'complainant_civil_status' => in_array(post_value('complainant_civil_status'), barangay_allowed_civil_statuses(), true) ? post_value('complainant_civil_status') : ($complaint['complainant_civil_status'] ?? ''),
    'complainant_address' => barangay_clean_address(post_value('complainant_address') ?: ($complaint['complainant_address'] ?? '')),
    'complainant_purok' => post_purok_value('complainant_purok', (string)($complaint['complainant_purok'] ?? '')),
    'complainant_contact' => post_phone_value('complainant_contact') ?: barangay_clean_phone($complaint['complainant_phone'] ?? ''),
    'respondent_name' => $respondentName,
    'respondent_age' => post_value('respondent_age'),
    'respondent_gender' => in_array(post_value('respondent_gender'), barangay_allowed_genders(), true) ? post_value('respondent_gender') : '',
    'respondent_civil_status' => in_array(post_value('respondent_civil_status'), barangay_allowed_civil_statuses(), true) ? post_value('respondent_civil_status') : '',
    'respondent_address' => barangay_clean_address(post_value('respondent_address')),
    'respondent_purok' => post_purok_value('respondent_purok'),
    'respondent_contact' => post_phone_value('respondent_contact'),
    'incident_date' => display_date(post_value('incident_date')),
    'incident_time' => display_time(post_value('incident_time')),
    'incident_place' => post_value('incident_place'),
    'complaint_types' => $complaintTypes,
    'complaint_type_other' => $complaintTypeOther,
    'statement_details' => post_value('statement_details') ?: $complaint['description'],
    'requested_actions' => $requestedActions,
    'other_action' => $otherAction,
    'witness_name' => post_value('witness_name'),
    'witness_address' => barangay_clean_address(post_value('witness_address')),
    'witness_purok' => post_purok_value('witness_purok'),
    'witness_contact' => post_phone_value('witness_contact'),
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

$requiredReportFields = [
    'province' => 'Province',
    'city' => 'City/Municipality',
    'barangay' => 'Barangay',
    'blotter_no' => 'Blotter number',
    'date_filed' => 'Date filed',
    'time_filed' => 'Time filed',
    'complainant_name' => 'Complainant full name',
    'complainant_age' => 'Complainant age',
    'complainant_gender' => 'Complainant gender',
    'complainant_civil_status' => 'Complainant civil status',
    'complainant_address' => 'Complainant address',
    'complainant_purok' => 'Complainant purok',
    'complainant_contact' => 'Complainant contact number',
    'respondent_name' => 'Person complained against full name',
    'respondent_age' => 'Person complained against age',
    'respondent_gender' => 'Person complained against gender',
    'respondent_civil_status' => 'Person complained against civil status',
    'respondent_address' => 'Person complained against address',
    'respondent_purok' => 'Person complained against purok',
    'respondent_contact' => 'Person complained against contact number',
    'incident_date' => 'Date of incident',
    'incident_time' => 'Time of incident',
    'incident_place' => 'Place of incident',
    'statement_details' => 'Statement of complaint',
    'action_date' => 'Date of barangay action',
    'recorded_by' => 'Recorded by',
    'recorded_position' => 'Recorded by position',
    'issued_day' => 'Issued day',
    'issued_month' => 'Issued month',
    'issued_year_suffix' => 'Issued year',
    'prepared_by' => 'Prepared by',
    'approved_by' => 'Approved by',
];

$missingFields = [];
foreach($requiredReportFields as $field => $label){
    if(trim((string)($reportData[$field] ?? '')) === ''){
        $missingFields[] = $label;
    }
}

if(intval($reportData['complainant_age']) < 18){
    $missingFields[] = 'Complainant must be 18 years old or above';
}

if(!barangay_is_ph_mobile($reportData['complainant_contact']) || !barangay_is_ph_mobile($reportData['respondent_contact'])){
    $missingFields[] = 'Contact numbers must be valid 11-digit Philippine mobile numbers';
}

$validComplaintTypes = ['Neighborhood Conflict', 'Minor Property Damage', 'Theft', 'Threat or Harassment', 'Physical/Verbal Dispute', 'Other'];
$complaintTypes = array_values(array_intersect($complaintTypes, $validComplaintTypes));
$reportData['complaint_types'] = $complaintTypes;

if(empty($complaintTypes)){
    $missingFields[] = 'At least one complaint type';
}

if(in_array('Other', $complaintTypes, true) && trim($complaintTypeOther) === ''){
    $missingFields[] = 'Other complaint type details';
}

$validRequestedActions = [
    'Record this incident in the barangay blotter',
    'Summon the respondent for mediation',
    'Assist both parties in settling the matter peacefully',
    'Issue a certification if needed',
    'Other',
];
$requestedActions = array_values(array_intersect($requestedActions, $validRequestedActions));
$reportData['requested_actions'] = $requestedActions;

if(empty($requestedActions)){
    $missingFields[] = 'At least one requested action';
}

if(in_array('Other', $requestedActions, true) && trim($otherAction) === ''){
    $missingFields[] = 'Other requested action details';
}

if(!empty($missingFields)){
    $_SESSION['staff_blotter_error'] = 'Please complete or correct: ' . implode(', ', array_unique($missingFields)) . '.';
    header("Location: view_complaints.php?" . http_build_query($redirectParams));
    exit();
}

$staffSignaturePdfPath = pdf_jpeg_path(!empty($staffSignature) ? 'uploads/signatures/' . $staffSignature : null, true);

$uploadsRoot = realpath(__DIR__ . '/../uploads');
$proofFolder = $uploadsRoot === false ? false : $uploadsRoot . DIRECTORY_SEPARATOR . 'complaint_proofs';

if($uploadsRoot === false || (!is_dir($proofFolder) && !mkdir($proofFolder, 0777, true))){
    header("Location: view_complaints.php?" . http_build_query($redirectParams));
    exit();
}

$storedFileName = 'blotter_' . $complaintId . '_' . $userId . '_' . time() . '.pdf';
$destinationPath = $proofFolder . DIRECTORY_SEPARATOR . $storedFileName;

if(!render_blotter_pdf($reportData, ['staff' => $staffSignaturePdfPath], $destinationPath)){
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

notify_user(
    $conn,
    intval($complaint['complainant_id']),
    'Blotter Signature Needed',
    'A barangay blotter report was generated. Please attach your cleaned JPG or PNG e-signature so the staff can submit it for admin approval.',
    '../complainant/my_complaints.php?status=In+Progress#complaint-' . $complaintId
);

header("Location: view_complaints.php?" . http_build_query($redirectParams));
exit();
?>
