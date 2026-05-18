<?php
session_start();

if(!headers_sent()){
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

if(!isset($_SESSION['user_id'], $_SESSION['role'])){
    header("Location: auth/login.php");
    exit();
}

include(__DIR__ . '/config/database.php');

function showProofMessage(string $title, string $message, int $statusCode = 404): void
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
            <button type="button" class="proof-close-button" onclick="history.back()" aria-label="Close proof">X</button>
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

function proofContentType(string $extension): ?string
{
    $contentTypes = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'mp4' => 'video/mp4',
        'mov' => 'video/quicktime',
        'webm' => 'video/webm',
    ];

    return $contentTypes[$extension] ?? null;
}

$attachmentId = intval($_GET['attachment_id'] ?? 0);
$updateId = intval($_GET['update_id'] ?? 0);

if($attachmentId > 0){
    $proof = db_select_one($conn,
    "SELECT complaint_update_attachments.attachment_id,
            complaint_update_attachments.update_id,
            complaint_update_attachments.stored_path,
            complaint_update_attachments.original_name,
            complaints.complainant_id,
            complaints.assigned_staff_id
     FROM complaint_update_attachments
     INNER JOIN complaint_updates ON complaint_updates.update_id = complaint_update_attachments.update_id
     INNER JOIN complaints ON complaints.complaint_id = complaint_updates.complaint_id
     WHERE complaint_update_attachments.attachment_id=?
     LIMIT 1",
     'i',
     [$attachmentId]);
} elseif($updateId > 0){
    $proof = db_select_one($conn,
    "SELECT complaint_update_attachments.attachment_id,
            complaint_update_attachments.update_id,
            complaint_update_attachments.stored_path,
            complaint_update_attachments.original_name,
            complaints.complainant_id,
            complaints.assigned_staff_id
     FROM complaint_update_attachments
     INNER JOIN complaint_updates ON complaint_updates.update_id = complaint_update_attachments.update_id
     INNER JOIN complaints ON complaints.complaint_id = complaint_updates.complaint_id
     WHERE complaint_update_attachments.update_id=?
     ORDER BY complaint_update_attachments.attachment_id ASC
     LIMIT 1",
     'i',
     [$updateId]);

    if(!$proof){
        $legacyProof = db_select_one($conn,
        "SELECT complaint_updates.update_id,
                complaint_updates.proof_path AS stored_path,
                complaint_updates.proof_original_name AS original_name,
                complaints.complainant_id,
                complaints.assigned_staff_id
         FROM complaint_updates
         INNER JOIN complaints ON complaints.complaint_id = complaint_updates.complaint_id
         WHERE complaint_updates.update_id=?
         LIMIT 1",
         'i',
         [$updateId]);
        $proof = $legacyProof;
    }
} else {
    $proof = null;
}

if(!$proof || empty($proof['stored_path'])){
    showProofMessage('Proof Not Found', 'No proof file is attached to this update.');
}

$userId = intval($_SESSION['user_id']);
$role = $_SESSION['role'];
$canView = false;

if($role === 'complainant'){
    $canView = intval($proof['complainant_id']) === $userId;
} elseif($role === 'staff'){
    $canView = intval($proof['assigned_staff_id']) === $userId;
} elseif(in_array($role, ['admin', 'superadmin'], true)){
    $canView = true;
}

if(!$canView){
    showProofMessage('Access Denied', 'You are not allowed to view this proof file.', 403);
}

$proofPath = trim((string)$proof['stored_path']);
$normalizedProofPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($proofPath, "/\\"));
$allowedFolder = realpath(__DIR__ . '/uploads/complaint_proofs');
$filePath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $normalizedProofPath);

if($allowedFolder === false || $filePath === false || strpos($filePath, $allowedFolder . DIRECTORY_SEPARATOR) !== 0 || !is_file($filePath)){
    showProofMessage(
        'Proof File Missing',
        'The database has a proof record, but the actual file is not in uploads/complaint_proofs on this server. Upload the proof file again or include the uploaded proof files when moving the system online.'
    );
}

$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$contentType = proofContentType($extension);

if(!$contentType){
    showProofMessage('Unsupported File', 'This proof file type cannot be opened here.', 415);
}

$displayName = $proof['original_name'] ?: basename($filePath);
$displayName = str_replace(['"', "\r", "\n"], '', $displayName);

if(isset($_GET['raw'])){
    if(ob_get_length()){
        ob_clean();
    }

    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . filesize($filePath));
    header('Content-Disposition: inline; filename="' . $displayName . '"');
    header('X-Content-Type-Options: nosniff');
    readfile($filePath);
    exit();
}

$rawQuery = $attachmentId > 0
    ? 'attachment_id=' . $attachmentId . '&raw=1'
    : 'update_id=' . intval($proof['update_id']) . '&raw=1';
$rawUrl = 'view_proof.php?' . $rawQuery;
$isImage = in_array($extension, ['jpg', 'jpeg', 'png'], true);
$isVideo = in_array($extension, ['mp4', 'mov', 'webm'], true);
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
        <button type="button" class="proof-close-button" onclick="history.back()" aria-label="Close proof">X</button>
        <div class="proof-viewer-shell">
            <div class="proof-viewer-header">
                <h1><?php echo htmlspecialchars($displayName); ?></h1>
            </div>

            <div class="proof-viewer-body">
                <?php if($isImage): ?>
                    <img src="<?php echo htmlspecialchars($rawUrl); ?>" alt="<?php echo htmlspecialchars($displayName); ?>">
                <?php elseif($isVideo): ?>
                    <video src="<?php echo htmlspecialchars($rawUrl); ?>" controls playsinline></video>
                <?php elseif($extension === 'pdf'): ?>
                    <iframe src="<?php echo htmlspecialchars($rawUrl); ?>" title="<?php echo htmlspecialchars($displayName); ?>"></iframe>
                <?php else: ?>
                    <a class="page-action" href="<?php echo htmlspecialchars($rawUrl); ?>">Open File</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
