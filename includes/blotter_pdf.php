<?php

require_once __DIR__ . '/simple_pdf.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/pdf_header_image.php';

function blotter_pdf_jpeg_path(?string $relativePath, bool $cleanSignature = false): ?string
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

function blotter_pdf_signature_block(SimplePdf $pdf, string $label, string $name, string $date = '', ?string $signaturePath = null): void
{
    $pdf->keepTogether(92);
    $pdf->line($label . ':');
    $signatureY = $pdf->getY();

    $pdf->text('Signature:', 72, $signatureY);
    $pdf->horizontalLine(170, $signatureY - 3, 445);

    if($signaturePath){
        $pdf->image($signaturePath, 285, max(72, $signatureY - 0), 104, 36);
    }

    $pdf->setY($signatureY - 16);
    $pdf->labelValue('Name', $name);
    $pdf->labelValue('Date', $date);
    $pdf->blank(10);
}

function blotter_pdf_selected_text(array $selected, string $otherValue = '', string $otherLabel = 'Other'): string
{
    $items = array_values(array_filter($selected, fn($item) => trim((string)$item) !== '' && $item !== 'Other'));

    if(trim($otherValue) !== ''){
        $items[] = $otherLabel . ': ' . trim($otherValue);
    } elseif(in_array('Other', $selected, true)){
        $items[] = $otherLabel;
    }

    return !empty($items) ? implode('; ', $items) : 'None selected';
}

function render_blotter_pdf(array $data, array $signatures, string $destinationPath): bool
{
    $pdf = new SimplePdf();
    $headerImage = pdf_header_image_path($data['province'] ?? '', $data['city'] ?? '', $data['barangay'] ?? '');

    if($headerImage){
        $pdf->image($headerImage, 71, 646, 470, 92);
    } else {
        $citySeal = blotter_pdf_jpeg_path('uploads/system/tangub_off_seal.jpg');
        $provinceSeal = blotter_pdf_jpeg_path('uploads/system/mis_occ_official_seal.jpg');

        if($citySeal){
            $pdf->image($citySeal, 150, 674, 62, 62);
        }

        if($provinceSeal){
            $pdf->image($provinceSeal, 400, 674, 62, 62);
        }

        $pdf->setY(710);
        $pdf->setFontSize(11);
        $pdf->center('Republic of the Philippines');
        $pdf->center('Province of ' . (($data['province'] ?? '') ?: '____________________'));
        $pdf->center('City/Municipality of ' . (($data['city'] ?? '') ?: '____________________'));
        $pdf->center('Barangay ' . (($data['barangay'] ?? '') ?: '____________________'));
        $pdf->center('Office of the Punong Barangay');
    }

    $pdf->setY(608);
    $pdf->setFontSize(12);
    $pdf->line('BARANGAY BLOTTER / COMPLAINT REPORT');
    $pdf->blank(10);
    $pdf->labelValue('Blotter No.', $data['blotter_no'] ?? '');
    $pdf->labelValue('Date Filed', $data['date_filed'] ?? '');
    $pdf->labelValue('Time Filed', $data['time_filed'] ?? '');
    $pdf->blank(14);

    $pdf->line('I. COMPLAINANT INFORMATION');
    $pdf->blank(6);
    $pdf->labelValue('Full Name', $data['complainant_name'] ?? '');
    $pdf->labelValue('Age', $data['complainant_age'] ?? '');
    $pdf->labelValue('Gender', $data['complainant_gender'] ?? '');
    $pdf->labelValue('Civil Status', $data['complainant_civil_status'] ?? '');
    $pdf->labelValue('Address', $data['complainant_address'] ?? '');
    $pdf->labelValue('Purok', !empty($data['complainant_purok']) ? 'Purok ' . $data['complainant_purok'] : '');
    $pdf->labelValue('Contact Number', $data['complainant_contact'] ?? '');
    $pdf->blank(12);

    $pdf->line('II. PERSON COMPLAINED AGAINST');
    $pdf->blank(6);
    $pdf->labelValue('Full Name', $data['respondent_name'] ?? '');
    $pdf->labelValue('Age', $data['respondent_age'] ?? '');
    $pdf->labelValue('Gender', $data['respondent_gender'] ?? '');
    $pdf->labelValue('Civil Status', $data['respondent_civil_status'] ?? '');
    $pdf->labelValue('Address', $data['respondent_address'] ?? '');
    $pdf->labelValue('Purok', !empty($data['respondent_purok']) ? 'Purok ' . $data['respondent_purok'] : '');
    $pdf->labelValue('Contact Number', $data['respondent_contact'] ?? '');
    $pdf->blank(12);

    $complaintTypes = $data['complaint_types'] ?? [];
    $pdf->line('III. INCIDENT DETAILS');
    $pdf->blank(6);
    $pdf->labelValue('Date of Incident', $data['incident_date'] ?? '');
    $pdf->labelValue('Time of Incident', $data['incident_time'] ?? '');
    $pdf->labelValue('Place of Incident', $data['incident_place'] ?? '');
    $pdf->paragraph('Type of Complaint: ' . blotter_pdf_selected_text($complaintTypes, $data['complaint_type_other'] ?? ''));
    $pdf->blank();

    $pdf->addPage();
    $pdf->line('IV. STATEMENT OF COMPLAINT');
    $pdf->blank(8);
    $residentAddress = trim((!empty($data['complainant_purok']) ? 'Purok ' . $data['complainant_purok'] . ', ' : '') . (($data['complainant_address'] ?? '') ?: ''));
    $pdf->paragraph('I, ' . (($data['complainant_name'] ?? '') ?: '____________________') . ', of legal age and a resident of ' . ($residentAddress ?: '____________________') . ', respectfully file this complaint before the Barangay against ' . (($data['respondent_name'] ?? '') ?: '____________________') . '.', true);
    $pdf->blank(4);
    $pdf->paragraph('On ' . (($data['incident_date'] ?? '') ?: '____________________') . ', at around ' . (($data['incident_time'] ?? '') ?: '____________________') . ', the incident happened at ' . (($data['incident_place'] ?? '') ?: '____________________') . '.', true);
    $pdf->blank(4);
    $pdf->line('The details of the complaint are as follows:');
    $pdf->paragraph($data['statement_details'] ?? '', true);
    $pdf->blank(4);
    $pdf->paragraph('Because of this incident, I am requesting the assistance of the Barangay to properly record this matter in the barangay blotter and to take the necessary action according to barangay rules and procedures.', true);
    $pdf->blank(18);

    $requestedActions = $data['requested_actions'] ?? [];
    $pdf->line('V. REQUESTED ACTION');
    $pdf->blank(8);
    $pdf->paragraph('Selected: ' . blotter_pdf_selected_text($requestedActions, $data['other_action'] ?? '', 'Take other proper action'));
    $pdf->blank(18);

    $pdf->line('VI. WITNESS INFORMATION');
    $pdf->blank(8);
    $pdf->labelValue('Name of Witness', $data['witness_name'] ?? '');
    $pdf->labelValue('Address', $data['witness_address'] ?? '');
    $pdf->labelValue('Purok', !empty($data['witness_purok']) ? 'Purok ' . $data['witness_purok'] : '');
    $pdf->labelValue('Contact Number', $data['witness_contact'] ?? '');
    $pdf->line('Statement of Witness:');
    $pdf->paragraph($data['witness_statement'] ?? '');
    $pdf->blank(18);

    $pdf->line('VII. ACTION TAKEN BY THE BARANGAY');
    $pdf->blank(8);
    $pdf->labelValue('Date of Action', $data['action_date'] ?? '');
    $pdf->labelValue('Remarks', $data['action_remarks'] ?? '');
    $pdf->blank(18);

    $pdf->addPage();
    $pdf->line('VIII. SIGNATURES');
    $pdf->blank(8);
    blotter_pdf_signature_block($pdf, 'Complainant', $data['complainant_name'] ?? '', $signatures['complainant_date'] ?? '', $signatures['complainant'] ?? null);
    blotter_pdf_signature_block($pdf, 'Received and Recorded By', $data['recorded_by'] ?? '', $data['date_filed'] ?? '', $signatures['staff'] ?? null);
    $pdf->labelValue('Position', $data['recorded_position'] ?? 'Barangay Secretary / Desk Officer');
    $pdf->blank(10);
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
        'staff' => blotter_pdf_jpeg_path(!empty($report['staff_signature_image']) ? 'uploads/signatures/' . $report['staff_signature_image'] : null, true),
        'complainant' => blotter_pdf_jpeg_path(!empty($report['complainant_signature_image']) ? 'uploads/blotter_signatures/' . $report['complainant_signature_image'] : null, true),
        'admin' => blotter_pdf_jpeg_path(!empty($report['admin_signature_image']) ? 'uploads/signatures/' . $report['admin_signature_image'] : null, true),
        'complainant_date' => !empty($report['complainant_signature_image']) ? date('F j, Y') : '',
        'admin_date' => !empty($report['admin_signature_image']) ? date('F j, Y') : '',
    ];

    $signatures = array_merge($signatures, $extraSignatures);
    $destinationPath = realpath(__DIR__ . '/../uploads');
    $destinationPath = $destinationPath === false ? false : $destinationPath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, preg_replace('/^uploads[\/\\\\]/', '', $report['report_path']));

    return $destinationPath ? render_blotter_pdf($data, $signatures, $destinationPath) : false;
}
?>
