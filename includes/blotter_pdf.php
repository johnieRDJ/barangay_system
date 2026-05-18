<?php

require_once __DIR__ . '/simple_pdf.php';

function blotter_pdf_jpeg_path(?string $relativePath): ?string
{
    if(!$relativePath){
        return null;
    }

    $path = realpath(__DIR__ . '/../' . ltrim($relativePath, '/\\'));
    $extension = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : '';

    return $path && in_array($extension, ['jpg', 'jpeg'], true) ? $path : null;
}

function blotter_pdf_signature_block(SimplePdf $pdf, string $label, string $name, string $date = '', ?string $signaturePath = null): void
{
    $pdf->line($label . ':');
    $lineY = $pdf->getY() + 2;

    if($signaturePath){
        $pdf->image($signaturePath, 300, max(72, $lineY), 46, 18);
    }

    $pdf->labelValue('Signature', '');
    $pdf->labelValue('Name', $name);
    $pdf->labelValue('Date', $date);
    $pdf->blank(8);
}

function render_blotter_pdf(array $data, array $signatures, string $destinationPath): bool
{
    $pdf = new SimplePdf();
    $citySeal = blotter_pdf_jpeg_path('uploads/system/tangub_off_seal.jpg');
    $provinceSeal = blotter_pdf_jpeg_path('uploads/system/mis_occ_official_seal.jpg');

    if($citySeal){
        $pdf->image($citySeal, 118, 672, 72);
    }

    if($provinceSeal){
        $pdf->image($provinceSeal, 434, 678, 58);
    }

    $pdf->setY(710);
    $pdf->setFontSize(11);
    $pdf->center('Republic of the Philippines');
    $pdf->center('Province of ' . (($data['province'] ?? '') ?: '____________________'));
    $pdf->center('City/Municipality of ' . (($data['city'] ?? '') ?: '____________________'));
    $pdf->center('Barangay ' . (($data['barangay'] ?? '') ?: '____________________'));
    $pdf->center('Office of the Punong Barangay');
    $pdf->blank(12);
    $pdf->setFontSize(12);
    $pdf->line('BARANGAY BLOTTER / COMPLAINT REPORT');
    $pdf->blank(12);
    $pdf->labelValue('Blotter No.', $data['blotter_no'] ?? '');
    $pdf->labelValue('Date Filed', $data['date_filed'] ?? '');
    $pdf->labelValue('Time Filed', $data['time_filed'] ?? '');
    $pdf->blank();

    $pdf->line('I. COMPLAINANT INFORMATION');
    $pdf->blank(8);
    $pdf->labelValue('Full Name', $data['complainant_name'] ?? '');
    $pdf->labelValue('Age', $data['complainant_age'] ?? '');
    $pdf->labelValue('Gender', $data['complainant_gender'] ?? '');
    $pdf->labelValue('Civil Status', $data['complainant_civil_status'] ?? '');
    $pdf->labelValue('Address', $data['complainant_address'] ?? '');
    $pdf->labelValue('Contact Number', $data['complainant_contact'] ?? '');
    $pdf->blank();

    $pdf->line('II. PERSON COMPLAINED AGAINST');
    $pdf->blank(8);
    $pdf->labelValue('Full Name', $data['respondent_name'] ?? '');
    $pdf->labelValue('Age', $data['respondent_age'] ?? '');
    $pdf->labelValue('Gender', $data['respondent_gender'] ?? '');
    $pdf->labelValue('Civil Status', $data['respondent_civil_status'] ?? '');
    $pdf->labelValue('Address', $data['respondent_address'] ?? '');
    $pdf->labelValue('Contact Number', $data['respondent_contact'] ?? '');
    $pdf->blank();

    $complaintTypes = $data['complaint_types'] ?? [];
    $pdf->line('III. INCIDENT DETAILS');
    $pdf->blank(8);
    $pdf->labelValue('Date of Incident', $data['incident_date'] ?? '');
    $pdf->labelValue('Time of Incident', $data['incident_time'] ?? '');
    $pdf->labelValue('Place of Incident', $data['incident_place'] ?? '');
    $pdf->line('Type of Complaint:');
    $hasOtherComplaintType = in_array('Other', $complaintTypes, true) || trim((string)($data['complaint_type_other'] ?? '')) !== '';
    foreach(['Neighborhood Conflict', 'Minor Property Damage', 'Theft', 'Threat or Harassment', 'Physical/Verbal Dispute'] as $type){
        $pdf->line((in_array($type, $complaintTypes, true) ? '[x] ' : '[ ] ') . $type);
    }
    $pdf->line(($hasOtherComplaintType ? '[x] ' : '[ ] ') . 'Other: ' . ($data['complaint_type_other'] ?? ''));
    $pdf->blank();

    $pdf->addPage();
    $pdf->line('IV. STATEMENT OF COMPLAINT');
    $pdf->blank(8);
    $pdf->paragraph('I, ' . (($data['complainant_name'] ?? '') ?: '____________________') . ', of legal age and a resident of ' . (($data['complainant_address'] ?? '') ?: '____________________') . ', respectfully file this complaint before the Barangay against ' . (($data['respondent_name'] ?? '') ?: '____________________') . '.');
    $pdf->blank(4);
    $pdf->paragraph('On ' . (($data['incident_date'] ?? '') ?: '____________________') . ', at around ' . (($data['incident_time'] ?? '') ?: '____________________') . ', the incident happened at ' . (($data['incident_place'] ?? '') ?: '____________________') . '.');
    $pdf->blank(4);
    $pdf->line('The details of the complaint are as follows:');
    $pdf->paragraph($data['statement_details'] ?? '');
    $pdf->blank(4);
    $pdf->paragraph('Because of this incident, I am requesting the assistance of the Barangay to properly record this matter in the barangay blotter and to take the necessary action according to barangay rules and procedures.');
    $pdf->blank();

    $requestedActions = $data['requested_actions'] ?? [];
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
    $pdf->line((in_array('Other', $requestedActions, true) ? '[x] ' : '[ ] ') . 'Take other proper action: ' . ($data['other_action'] ?? ''));
    $pdf->blank();

    $pdf->line('VI. WITNESS INFORMATION');
    $pdf->blank(8);
    $pdf->labelValue('Name of Witness', $data['witness_name'] ?? '');
    $pdf->labelValue('Address', $data['witness_address'] ?? '');
    $pdf->labelValue('Contact Number', $data['witness_contact'] ?? '');
    $pdf->line('Statement of Witness:');
    $pdf->paragraph($data['witness_statement'] ?? '');
    $pdf->blank();

    $pdf->line('VII. ACTION TAKEN BY THE BARANGAY');
    $pdf->blank(8);
    $pdf->labelValue('Date of Action', $data['action_date'] ?? '');
    $pdf->labelValue('Remarks', $data['action_remarks'] ?? '');
    $pdf->blank();

    $pdf->addPage();
    $pdf->line('VIII. SIGNATURES');
    $pdf->blank(8);
    blotter_pdf_signature_block($pdf, 'Complainant', $data['complainant_name'] ?? '', $signatures['complainant_date'] ?? '', $signatures['complainant'] ?? null);
    blotter_pdf_signature_block($pdf, 'Received and Recorded By', $data['recorded_by'] ?? '', $data['date_filed'] ?? '', $signatures['staff'] ?? null);
    $pdf->labelValue('Position', $data['recorded_position'] ?? 'Barangay Secretary / Desk Officer');
    blotter_pdf_signature_block($pdf, 'Approved By', $data['approved_by'] ?? 'Punong Barangay', $signatures['admin_date'] ?? '', $signatures['admin'] ?? null);
    $pdf->blank();

    $pdf->line('CERTIFICATION');
    $pdf->paragraph('This is to certify that the above complaint was officially recorded in the Barangay Blotter of Barangay ' . (($data['barangay'] ?? '') ?: '____________________') . ' on ' . (($data['date_filed'] ?? '') ?: '____________________') . ' at ' . (($data['time_filed'] ?? '') ?: '____________________') . '.');
    $pdf->paragraph('Issued this ' . ($data['issued_day'] ?? '') . ' day of ' . ($data['issued_month'] ?? '') . ', 20' . ($data['issued_year_suffix'] ?? '') . ' at Barangay ' . (($data['barangay'] ?? '') ?: '____________________') . ', City/Municipality of ' . (($data['city'] ?? '') ?: '____________________') . '.');
    $pdf->blank(14);
    $pdf->line('Prepared by:');
    $pdf->line($data['prepared_by'] ?? 'Barangay Secretary / Desk Officer');
    $pdf->blank(18);
    $pdf->line('Approved by:');
    $pdf->line($data['approved_by'] ?? 'Punong Barangay');

    return $pdf->output($destinationPath);
}

function regenerate_blotter_report_pdf(mysqli $conn, int $reportId, array $extraSignatures = []): bool
{
    $report = db_select_one($conn,
    "SELECT * FROM blotter_reports WHERE report_id=? LIMIT 1",
    'i',
    [$reportId]);

    if(!$report || empty($report['report_data']) || empty($report['report_path'])){
        return false;
    }

    $data = json_decode($report['report_data'], true);
    if(!is_array($data)){
        return false;
    }

    $signatures = [
        'staff' => blotter_pdf_jpeg_path(!empty($report['staff_signature_image']) ? 'uploads/signatures/' . $report['staff_signature_image'] : null),
        'complainant' => blotter_pdf_jpeg_path(!empty($report['complainant_signature_image']) ? 'uploads/blotter_signatures/' . $report['complainant_signature_image'] : null),
        'admin' => blotter_pdf_jpeg_path(!empty($report['admin_signature_image']) ? 'uploads/signatures/' . $report['admin_signature_image'] : null),
        'complainant_date' => !empty($report['complainant_signature_image']) ? date('F j, Y') : '',
        'admin_date' => !empty($report['admin_signature_image']) ? date('F j, Y') : '',
    ];

    $signatures = array_merge($signatures, $extraSignatures);
    $destinationPath = realpath(__DIR__ . '/../uploads');
    $destinationPath = $destinationPath === false ? false : $destinationPath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, preg_replace('/^uploads[\/\\\\]/', '', $report['report_path']));

    return $destinationPath ? render_blotter_pdf($data, $signatures, $destinationPath) : false;
}
?>
