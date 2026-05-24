<?php
session_start();

if(!isset($_SESSION['user_id'], $_SESSION['role'])){
    header("Location: auth/login.php");
    exit();
}

include(__DIR__ . '/config/database.php');
include_once(__DIR__ . '/includes/blotter_pdf.php');

function showBlotterMessage(string $title, string $message, int $statusCode = 404): void
{
    http_response_code($statusCode);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo htmlspecialchars($title); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
        <?php $styleVersion = file_exists(__DIR__ . '/css/style.css') ? filemtime(__DIR__ . '/css/style.css') : time(); ?>
        <link rel="stylesheet" href="css/style.css?v=<?php echo $styleVersion; ?>">
    </head>
    <body>
        <div class="proof-viewer-page">
            <button type="button" class="proof-close-button" onclick="history.back()" aria-label="Close report">X</button>
            <div class="proof-message-card">
                <h1><?php echo htmlspecialchars($title); ?></h1>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$reportId = intval($_GET['report_id'] ?? 0);

if($reportId <= 0){
    showBlotterMessage('Report Not Found', 'No blotter report was selected.');
}

$report = db_select_one($conn,
"SELECT blotter_reports.*,
        complaints.complainant_id,
        complaints.assigned_staff_id
 FROM blotter_reports
 JOIN complaints ON blotter_reports.complaint_id = complaints.complaint_id
 WHERE blotter_reports.report_id=?
 LIMIT 1",
 'i',
 [$reportId]);

if(!$report || empty($report['report_path'])){
    showBlotterMessage('Report Not Found', 'This blotter report is not available.');
}

$userId = intval($_SESSION['user_id']);
$role = $_SESSION['role'];
$canView = false;

if($role === 'complainant'){
    $canView = intval($report['complainant_id']) === $userId;
} elseif($role === 'staff'){
    $canView = intval($report['assigned_staff_id']) === $userId;
} elseif(in_array($role, ['admin', 'superadmin'], true)){
    $canView = true;
}

if(!$canView){
    showBlotterMessage('Access Denied', 'You are not allowed to view this blotter report.', 403);
}

regenerate_blotter_report_pdf($conn, $reportId);

$normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($report['report_path'], "/\\"));
$allowedFolder = realpath(__DIR__ . '/uploads');
$filePath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $normalizedPath);

if($allowedFolder === false || $filePath === false || strpos($filePath, $allowedFolder . DIRECTORY_SEPARATOR) !== 0 || !is_file($filePath)){
    showBlotterMessage('Report File Missing', 'The report record exists, but the PDF file is missing from the uploads folder.');
}

$displayName = $report['report_original_name'] ?: basename($filePath);
$displayName = str_replace(['"', "\r", "\n"], '', $displayName);

if(isset($_GET['raw'])){
    if(ob_get_length()){
        ob_clean();
    }

    header('Content-Type: application/pdf');
    header('Content-Length: ' . filesize($filePath));
    header('Content-Disposition: inline; filename="' . $displayName . '"');
    header('X-Content-Type-Options: nosniff');
    readfile($filePath);
    exit();
}

$rawUrl = 'view_blotter_report.php?report_id=' . $reportId . '&raw=1';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($displayName); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <?php $styleVersion = file_exists(__DIR__ . '/css/style.css') ? filemtime(__DIR__ . '/css/style.css') : time(); ?>
    <link rel="stylesheet" href="css/style.css?v=<?php echo $styleVersion; ?>">
</head>
<body>
    <div class="proof-viewer-page">
        <button type="button" class="proof-close-button" onclick="history.back()" aria-label="Close report">X</button>
        <div class="proof-viewer-shell">
            <div class="proof-viewer-header">
                <h1><?php echo htmlspecialchars($displayName); ?></h1>
            </div>
            <div class="proof-viewer-body">
                <iframe src="<?php echo htmlspecialchars($rawUrl); ?>" title="<?php echo htmlspecialchars($displayName); ?>"></iframe>
            </div>
        </div>
    </div>
</body>
</html>
