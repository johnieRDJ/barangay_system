<?php

require_once __DIR__ . '/simple_pdf.php';
require_once __DIR__ . '/validation.php';

function paper_pdf_jpeg_path(?string $relativePath, bool $cleanSignature = false): ?string
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

function paper_pdf_header(SimplePdf $pdf, string $title, array $location = []): void
{
    $citySeal = paper_pdf_jpeg_path('uploads/system/tangub_off_seal.jpg');
    $provinceSeal = paper_pdf_jpeg_path('uploads/system/mis_occ_official_seal.jpg');

    if($citySeal){
        $pdf->image($citySeal, 126, 676, 62, 62);
    }

    if($provinceSeal){
        $pdf->image($provinceSeal, 424, 676, 62, 62);
    }

    $pdf->setY(710);
    $pdf->setFontSize(11);
    $pdf->center('Republic of the Philippines');
    $pdf->center('Province of ' . (($location['province'] ?? '') ?: 'Misamis Occidental'));
    $pdf->center('City/Municipality of ' . (($location['city'] ?? '') ?: 'Tangub'));
    $pdf->center('Barangay ' . (($location['barangay'] ?? '') ?: 'Labuyo'));
    $pdf->center('Office of the Punong Barangay');
    $pdf->blank(18);
    $pdf->setFontSize(12);
    $pdf->line($title);
    $pdf->blank(12);
}

function paper_pdf_signature(SimplePdf $pdf, string $label, string $name, ?string $signaturePath = null): void
{
    $pdf->line($label . ':');
    $signatureY = $pdf->getY();
    $pdf->text('Signature:', 72, $signatureY);
    $pdf->horizontalLine(170, $signatureY - 3, 445);

    if($signaturePath){
        $pdf->image($signaturePath, 280, max(72, $signatureY), 92, 32);
    }

    $pdf->setY($signatureY - 16);
    $pdf->labelValue('Name', $name);
    $pdf->labelValue('Date', date('F j, Y'));
    $pdf->blank(8);
}

function paper_pdf_stream(SimplePdf $pdf, string $fileName): void
{
    $tempPath = tempnam(sys_get_temp_dir(), 'paper_pdf_');

    if(!$tempPath || !$pdf->output($tempPath)){
        http_response_code(500);
        echo 'Could not generate PDF.';
        exit();
    }

    if(ob_get_length()){
        ob_clean();
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . str_replace(['"', "\r", "\n"], '', $fileName) . '"');
    header('Content-Length: ' . filesize($tempPath));
    header('X-Content-Type-Options: nosniff');
    readfile($tempPath);
    @unlink($tempPath);
    exit();
}
?>
