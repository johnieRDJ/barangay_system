<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/pagination.php');
include('../includes/complaint_updates.php');
include('../includes/send_complaint_update.php');

$user_id = intval($_SESSION['user_id']);
$update_error = '';

if(isset($_POST['update'])){

    $id = intval($_POST['complaint_id']);
    $comment = trim($_POST['comment']);
    $status = $_POST['status'] ?? 'In Progress';
    $postedPerPage = intval($_POST['per_page'] ?? 10);
    $postedPerPage = in_array($postedPerPage, [10, 20, 30, 40, 50], true) ? $postedPerPage : 10;
    $postedStatusFilter = $_POST['status_filter'] ?? '';
    $postedStatusFilter = in_array($postedStatusFilter, ['Pending', 'In Progress', 'Reopened', 'Awaiting Confirmation', 'Resolved'], true) ? $postedStatusFilter : '';
    $redirectParams = [
        'updated' => 1,
        'page' => max(1, intval($_POST['page'] ?? 1)),
        'per_page' => $postedPerPage,
    ];

    if($postedStatusFilter !== ''){
        $redirectParams['status'] = $postedStatusFilter;
    }

    $redirectQuery = http_build_query($redirectParams);

    if(!in_array($status, ['In Progress', 'Resolved'], true)){
        $status = 'In Progress';
    }

    $proofFiles = [];

    if(isset($_FILES['proof_files']) && is_array($_FILES['proof_files']['name'])){
        foreach($_FILES['proof_files']['name'] as $index => $name){
            $error = $_FILES['proof_files']['error'][$index] ?? UPLOAD_ERR_NO_FILE;

            if($error === UPLOAD_ERR_NO_FILE){
                continue;
            }

            $proofFiles[] = [
                'name' => $name,
                'tmp_name' => $_FILES['proof_files']['tmp_name'][$index] ?? '',
                'error' => $error,
                'size' => intval($_FILES['proof_files']['size'][$index] ?? 0),
            ];
        }
    }

    $hasUpload = count($proofFiles) > 0;
    $storedProofs = [];

    if($status === 'Resolved' && !$hasUpload){
        $update_error = 'At least one proof file is required before marking a complaint as resolved.';
    }

    if($comment === ''){
        $update_error = 'Please add a progress remark for the complainant.';
    }

    if($update_error === '' && $hasUpload){
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'mp4', 'mov', 'webm'];

        if(count($proofFiles) > 6){
            $update_error = 'You can attach up to 6 proof files per update.';
        }

        foreach($proofFiles as $proofFile){
            $extension = strtolower(pathinfo($proofFile['name'], PATHINFO_EXTENSION));
            $maxSize = in_array($extension, ['mp4', 'mov', 'webm'], true) ? 50 * 1024 * 1024 : 10 * 1024 * 1024;

            if($proofFile['error'] !== UPLOAD_ERR_OK){
                $update_error = 'One of the proof files could not be uploaded. Please try again.';
                break;
            }

            if(!in_array($extension, $allowedExtensions, true)){
                $update_error = 'Only JPG, PNG, PDF, MP4, MOV, and WEBM files are allowed as proof.';
                break;
            }

            if($proofFile['size'] > $maxSize){
                $update_error = 'Images and PDFs must be 10MB or smaller. Videos must be 50MB or smaller.';
                break;
            }
        }

        if($update_error === ''){
            $uploadsRoot = realpath(__DIR__ . '/../uploads');
            $proofFolder = $uploadsRoot === false ? false : $uploadsRoot . DIRECTORY_SEPARATOR . 'complaint_proofs';

            if($uploadsRoot === false){
                $update_error = 'Upload directory is not available.';
            } elseif(!is_dir($proofFolder) && !mkdir($proofFolder, 0777, true)){
                $update_error = 'Could not create the proof upload folder.';
            } else {
                foreach($proofFiles as $index => $proofFile){
                    $safeFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($proofFile['name']));
                    $extension = strtolower(pathinfo($safeFileName, PATHINFO_EXTENSION));
                    $storedFileName = time() . '_' . $id . '_' . $user_id . '_' . $index . '_' . $safeFileName;
                    $destinationPath = $proofFolder . DIRECTORY_SEPARATOR . $storedFileName;

                    if(move_uploaded_file($proofFile['tmp_name'], $destinationPath)){
                        $storedProofs[] = [
                            'stored_path' => 'uploads/complaint_proofs/' . $storedFileName,
                            'original_name' => $proofFile['name'],
                            'file_type' => $extension,
                            'file_size' => $proofFile['size'],
                        ];
                    } else {
                        $update_error = 'Could not save one of the proof files.';
                        break;
                    }
                }
            }
        }
    }

    if($update_error === ''){
        $resolutionConfirmationValue = $status === 'Resolved' ? 'pending' : null;
        $complaintNotice = db_select_one($conn,
        "SELECT complaints.tracking_number,
                complaints.subject,
                users.email,
                users.firstname,
                users.lastname
         FROM complaints
         JOIN users ON complaints.complainant_id = users.user_id
         WHERE complaints.complaint_id=?
         AND complaints.assigned_staff_id=?
         LIMIT 1",
         'ii',
         [$id, $user_id]);

        if($status === 'Resolved'){
            $stmt = db_prepared_query($conn,
            "UPDATE complaints
             SET status=?,
                 staff_comment=?,
                 resolution_confirmation=?
             WHERE complaint_id=?
             AND assigned_staff_id=?",
             'sssii',
             [$status, $comment, $resolutionConfirmationValue, $id, $user_id]);
        } else {
            $stmt = db_prepared_query($conn,
            "UPDATE complaints
             SET status=?,
                 staff_comment=?,
                 resolution_confirmation=resolution_confirmation
             WHERE complaint_id=?
             AND assigned_staff_id=?",
             'ssii',
             [$status, $comment, $id, $user_id]);
        }

        $updated = $stmt ? mysqli_stmt_affected_rows($stmt) : 0;
        if($stmt){
            mysqli_stmt_close($stmt);
        }

        if($updated > 0){
            $updateId = addComplaintUpdate(
                $conn,
                $id,
                $user_id,
                'staff',
                $status === 'Resolved' ? 'resolved' : 'progress_update',
                $status,
                $comment
            );

            if($updateId){
                foreach($storedProofs as $storedProof){
                    addComplaintUpdateAttachment(
                        $conn,
                        $updateId,
                        $storedProof['stored_path'],
                        $storedProof['original_name'],
                        $storedProof['file_type'],
                        $storedProof['file_size']
                    );
                }
            }

            $log_action = $status === 'Resolved'
                ? "Resolved complaint ID $id and added comment"
                : "Updated complaint ID $id with progress remarks";

            db_execute($conn,
            "INSERT INTO logs (user_id, action)
             VALUES (?, ?)",
             'is',
             [$user_id, $log_action]);

            if($complaintNotice){
                $fullname = trim($complaintNotice['firstname'] . ' ' . $complaintNotice['lastname']);
                $emailStatus = $status === 'Resolved'
                    ? 'Resolved - Awaiting Your Confirmation'
                    : $status;
                $emailMessage = $comment;

                if($status === 'Resolved'){
                    $emailMessage .= "\n\nPlease open your complaint timeline and confirm if the issue is truly resolved.";
                }

                sendComplaintTimelineUpdate(
                    $complaintNotice['email'],
                    $fullname,
                    $complaintNotice['subject'],
                    $complaintNotice['tracking_number'],
                    $emailStatus,
                    $emailMessage,
                    'Barangay Staff'
                );
            }
        }

        header("Location: view_complaints.php?$redirectQuery");
        exit();
    }
}

include('../includes/header.php');
include('../includes/sidebar.php');

//  LOG: Viewed assigned complaints
db_execute($conn,
"INSERT INTO logs (user_id, action)
 VALUES (?, ?)",
 'is',
 [$user_id, 'Viewed assigned complaints']);

$statusFilter = $_GET['status'] ?? '';
$allowedStatusFilters = ['Pending', 'In Progress', 'Reopened', 'Awaiting Confirmation', 'Resolved'];
$statusTabs = [
    '' => 'All',
    'Pending' => 'Pending',
    'In Progress' => 'In Progress',
    'Reopened' => 'Reopened',
    'Awaiting Confirmation' => 'Awaiting',
    'Resolved' => 'Resolved',
];

if(!in_array($statusFilter, $allowedStatusFilters, true)){
    $statusFilter = '';
}

$whereSql = "complaints.assigned_staff_id=?";
$types = 'i';
$params = [$user_id];

if($statusFilter === 'Reopened'){
    $whereSql .= " AND complaints.status='In Progress' AND complaints.resolution_confirmation='reopened'";
} elseif($statusFilter === 'Awaiting Confirmation'){
    $whereSql .= " AND complaints.status='Resolved' AND complaints.resolution_confirmation='pending'";
} elseif($statusFilter === 'Resolved'){
    $whereSql .= " AND complaints.status='Resolved' AND (complaints.resolution_confirmation='confirmed' OR complaints.resolution_confirmation IS NULL)";
} elseif($statusFilter === 'In Progress'){
    $whereSql .= " AND complaints.status='In Progress' AND (complaints.resolution_confirmation IS NULL OR complaints.resolution_confirmation!='reopened')";
} elseif($statusFilter !== ''){
    $whereSql .= " AND complaints.status=?";
    $types .= 's';
    $params[] = $statusFilter;
}

$statusOrderSql = "CASE
    WHEN complaints.status='Pending' THEN 1
    WHEN complaints.status='In Progress' AND (complaints.resolution_confirmation IS NULL OR complaints.resolution_confirmation!='reopened') THEN 2
    WHEN complaints.status='In Progress' AND complaints.resolution_confirmation='reopened' THEN 3
    WHEN complaints.status='Resolved' AND complaints.resolution_confirmation='pending' THEN 4
    WHEN complaints.status='Resolved' THEN 5
    ELSE 6
 END";

$pagination = pagination_state($conn,
"SELECT COUNT(*) AS total
 FROM complaints
 LEFT JOIN users u ON complaints.complainant_id = u.user_id
 WHERE $whereSql",
 $types,
 $params);

$complaints = db_select_all($conn,
"SELECT complaints.*,
        u.firstname AS complainant_firstname,
        u.lastname AS complainant_lastname,
        u.email AS complainant_email,
        user_profiles.address AS complainant_address,
        user_profiles.phone AS complainant_phone,
        user_profiles.age AS complainant_age,
        user_profiles.gender AS complainant_gender,
        user_profiles.civil_status AS complainant_civil_status
 FROM complaints
 LEFT JOIN users u ON complaints.complainant_id = u.user_id
 LEFT JOIN user_profiles ON u.user_id = user_profiles.user_id
 WHERE $whereSql
 ORDER BY $statusOrderSql, complaints.complaint_id DESC" . $pagination['limit_sql'],
 $types,
 $params);

$complaintIds = array_map('intval', array_column($complaints, 'complaint_id'));
$reopenReasonRows = [];

if(!empty($complaintIds)){
    $placeholders = implode(',', array_fill(0, count($complaintIds), '?'));
    $reopenReasonRows = db_select_all($conn,
    "SELECT complaint_updates.complaint_id,
            complaint_updates.message,
            complaint_updates.created_at
     FROM complaint_updates
     WHERE complaint_updates.complaint_id IN ($placeholders)
     AND complaint_updates.update_type='resolution_reopened'
     ORDER BY complaint_updates.created_at DESC, complaint_updates.update_id DESC",
     str_repeat('i', count($complaintIds)),
     $complaintIds);
}

$latestReopenReasonByComplaint = [];
foreach($reopenReasonRows as $reopenRow){
    $reopenedComplaintId = intval($reopenRow['complaint_id']);

    if(!isset($latestReopenReasonByComplaint[$reopenedComplaintId])){
        $latestReopenReasonByComplaint[$reopenedComplaintId] = $reopenRow;
    }
}

$accountRows = db_select_all($conn,
"SELECT users.user_id,
        users.firstname,
        users.lastname,
        users.email,
        user_profiles.address,
        user_profiles.phone,
        user_profiles.age,
        user_profiles.gender,
        user_profiles.civil_status
 FROM users
 LEFT JOIN user_profiles ON users.user_id = user_profiles.user_id
 WHERE users.role != 'superadmin'
 ORDER BY users.lastname, users.firstname");
?>

<div class="page-shell">
    <div class="dashboard-header">
        <h1>Assigned Complaints</h1>
        <p>Post progress remarks and proof files so complainants can see real work happening on their concern.</p>
    </div>

    <?php if($update_error !== ''): ?>
        <div class="table-card">
            <p style="margin:0; color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($update_error); ?></p>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['updated'])): ?>
        <div class="table-card">
            <p style="margin:0; color:#15803d; font-weight:600;">Complaint progress updated successfully.</p>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['blotter'])): ?>
        <div class="table-card">
            <p style="margin:0; color:#15803d; font-weight:600;">Barangay blotter report generated and attached to the complaint timeline.</p>
        </div>
    <?php endif; ?>

    <nav class="status-tabs" aria-label="Assigned complaint status filters">
        <?php foreach($statusTabs as $tabValue => $tabLabel): ?>
            <?php
            $tabQuery = [
                'page' => 1,
                'per_page' => intval($pagination['per_page']),
            ];

            if($tabValue !== ''){
                $tabQuery['status'] = $tabValue;
            }

            $isActiveTab = $statusFilter === $tabValue;
            ?>
            <a href="view_complaints.php?<?php echo htmlspecialchars(http_build_query($tabQuery)); ?>" class="<?php echo $isActiveTab ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($tabLabel); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="table-card">
        <table border="1" cellpadding="10" width="100%" class="responsive-table staff-complaints-table">
            <tr>
                <th>Complainant</th>
                <th>Tracking</th>
                <th>Subject</th>
                <th>Description</th>
                <th>Status</th>
                <th>Staff Update</th>
                <th>Action</th>
            </tr>

            <?php foreach($complaints as $row): ?>
                <?php
                $complaintId = intval($row['complaint_id']);
                $latestReopenReason = $latestReopenReasonByComplaint[$complaintId] ?? null;
                ?>
                <tr>
                    <td data-label="Complainant"><?php echo htmlspecialchars(trim(($row['complainant_firstname'] ?? '') . ' ' . ($row['complainant_lastname'] ?? ''))); ?></td>
                    <td data-label="Tracking"><span class="tracking-number compact"><?php echo htmlspecialchars($row['tracking_number']); ?></span></td>
                    <td data-label="Subject"><?php echo htmlspecialchars($row['subject']); ?></td>
                    <td data-label="Description"><?php echo htmlspecialchars($row['description']); ?></td>
                    <td data-label="Status">
                        <?php if($row['status'] === 'Pending'): ?>
                            <span class="status-badge status-pending">Pending</span>
                        <?php elseif($row['status'] === 'Resolved' && $row['resolution_confirmation'] === 'pending'): ?>
                            <span class="status-badge complaint-status-awaiting">Awaiting Confirmation</span>
                        <?php elseif($row['status'] === 'In Progress' && $row['resolution_confirmation'] === 'reopened'): ?>
                            <span class="status-badge complaint-status-reopened">Reopened</span>
                        <?php elseif($row['status'] === 'In Progress'): ?>
                            <span class="status-badge complaint-status-progress">In Progress</span>
                        <?php else: ?>
                            <span class="status-badge status-approved">Resolved</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Staff Update">
                        <?php echo !empty($row['staff_comment']) ? nl2br(htmlspecialchars($row['staff_comment'])) : '<span class="table-muted">No comment yet</span>'; ?>
                        <?php if($latestReopenReason): ?>
                            <div class="reopen-reason-box">
                                <strong>Complainant reason for reopening:</strong>
                                <p><?php echo nl2br(htmlspecialchars($latestReopenReason['message'])); ?></p>
                                <span><?php echo date('F j, Y g:i A', strtotime($latestReopenReason['created_at'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="action-cell" data-label="Action">
                        <a href="../reports/print_complaint_record.php?id=<?php echo $complaintId; ?>" class="page-action">Print Record</a>
                        <button type="button" class="secondary-action" onclick="document.getElementById('blotter-modal-<?php echo $complaintId; ?>').showModal()">Blotter Report</button>
                        <?php if($row['status'] === 'Resolved' && $row['resolution_confirmation'] === 'pending'): ?>
                            <span class="table-muted">Waiting for complainant confirmation</span>
                        <?php elseif($row['status'] === 'Resolved'): ?>
                            <span class="table-muted">Already resolved</span>
                        <?php else: ?>
                            <form method="POST" enctype="multipart/form-data" class="complaint-update-form">
                                <input type="hidden" name="complaint_id" value="<?php echo $complaintId; ?>">
                                <input type="hidden" name="page" value="<?php echo intval($pagination['page']); ?>">
                                <input type="hidden" name="per_page" value="<?php echo intval($pagination['per_page']); ?>">
                                <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($statusFilter); ?>">
                                <select name="status" required>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Resolved">Resolved</option>
                                </select>
                                <textarea name="comment" placeholder="Add an update the complainant can see..." required></textarea>
                                <input type="file" name="proof_files[]" accept=".jpg,.jpeg,.png,.pdf,.mp4,.mov,.webm" multiple>
                                <p class="table-muted" style="margin:0;">Attach up to 6 photos, PDFs, or videos. Required for resolved complaints.</p>
                                <button type="submit" name="update">Save Update</button>
                            </form>
                        <?php endif; ?>

                        <dialog class="blotter-modal" id="blotter-modal-<?php echo $complaintId; ?>">
                            <form method="POST" action="generate_blotter.php" class="blotter-form">
                                <input type="hidden" name="complaint_id" value="<?php echo $complaintId; ?>">
                                <input type="hidden" name="page" value="<?php echo intval($pagination['page']); ?>">
                                <input type="hidden" name="per_page" value="<?php echo intval($pagination['per_page']); ?>">
                                <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($statusFilter); ?>">

                                <div class="modal-header-row">
                                    <h2>Barangay Blotter / Complaint Report</h2>
                                    <button type="button" class="secondary-action modal-close-button" onclick="this.closest('dialog').close()">X</button>
                                </div>

                                <div class="blotter-grid">
                                    <label>Province<input name="province" type="text"></label>
                                    <label>City/Municipality<input name="city" type="text"></label>
                                    <label>Barangay<input name="barangay" type="text"></label>
                                    <label>Blotter No.<input name="blotter_no" type="text" value="<?php echo htmlspecialchars($row['tracking_number']); ?>"></label>
                                    <label>Date Filed<input name="date_filed" type="date" value="<?php echo date('Y-m-d'); ?>"></label>
                                    <label>Time Filed<input name="time_filed" type="time" value="<?php echo date('H:i'); ?>"></label>
                                </div>

                                <h3>Complainant Information</h3>
                                <div class="blotter-grid">
                                    <label>Full Name<input name="complainant_name" type="text" value="<?php echo htmlspecialchars(trim(($row['complainant_firstname'] ?? '') . ' ' . ($row['complainant_lastname'] ?? ''))); ?>"></label>
                                    <label>Age<input name="complainant_age" type="text" value="<?php echo htmlspecialchars($row['complainant_age'] ?? ''); ?>"></label>
                                    <label>Gender<input name="complainant_gender" type="text" value="<?php echo htmlspecialchars($row['complainant_gender'] ?? ''); ?>"></label>
                                    <label>Civil Status<input name="complainant_civil_status" type="text" value="<?php echo htmlspecialchars($row['complainant_civil_status'] ?? ''); ?>"></label>
                                    <label>Address<input name="complainant_address" type="text" value="<?php echo htmlspecialchars($row['complainant_address'] ?? ''); ?>"></label>
                                    <label>Contact Number<input name="complainant_contact" type="text" value="<?php echo htmlspecialchars($row['complainant_phone'] ?? ''); ?>"></label>
                                </div>

                                <h3>Person Complained Against</h3>
                                <label>Use Existing Account
                                    <select class="respondent-account-select">
                                        <option value="">Manual Entry</option>
                                        <?php foreach($accountRows as $account): ?>
                                            <option
                                                value="<?php echo intval($account['user_id']); ?>"
                                                data-name="<?php echo htmlspecialchars(trim($account['firstname'] . ' ' . $account['lastname'])); ?>"
                                                data-age="<?php echo htmlspecialchars($account['age'] ?? ''); ?>"
                                                data-gender="<?php echo htmlspecialchars($account['gender'] ?? ''); ?>"
                                                data-civil-status="<?php echo htmlspecialchars($account['civil_status'] ?? ''); ?>"
                                                data-address="<?php echo htmlspecialchars($account['address'] ?? ''); ?>"
                                                data-contact="<?php echo htmlspecialchars($account['phone'] ?? ''); ?>">
                                                <?php echo htmlspecialchars(trim($account['firstname'] . ' ' . $account['lastname']) . ' - ' . $account['email']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <div class="blotter-grid">
                                    <label>Full Name<input name="respondent_name" data-respondent-field="name" type="text"></label>
                                    <label>Age<input name="respondent_age" data-respondent-field="age" type="text"></label>
                                    <label>Gender<input name="respondent_gender" data-respondent-field="gender" type="text"></label>
                                    <label>Civil Status<input name="respondent_civil_status" data-respondent-field="civilStatus" type="text"></label>
                                    <label>Address<input name="respondent_address" data-respondent-field="address" type="text"></label>
                                    <label>Contact Number<input name="respondent_contact" data-respondent-field="contact" type="text"></label>
                                </div>

                                <h3>Incident Details</h3>
                                <div class="blotter-grid">
                                    <label>Date of Incident<input name="incident_date" type="date"></label>
                                    <label>Time of Incident<input name="incident_time" type="time"></label>
                                    <label>Place of Incident<input name="incident_place" type="text"></label>
                                </div>

                                <div class="blotter-checks">
                                    <label><input type="checkbox" name="complaint_types[]" value="Neighborhood Conflict"> Neighborhood Conflict</label>
                                    <label><input type="checkbox" name="complaint_types[]" value="Minor Property Damage"> Minor Property Damage</label>
                                    <label><input type="checkbox" name="complaint_types[]" value="Theft"> Theft</label>
                                    <label><input type="checkbox" name="complaint_types[]" value="Threat or Harassment"> Threat or Harassment</label>
                                    <label><input type="checkbox" name="complaint_types[]" value="Physical/Verbal Dispute"> Physical/Verbal Dispute</label>
                                    <label><input type="checkbox" name="complaint_types[]" value="Other"> Other</label>
                                </div>
                                <label>Other Complaint Type<input name="complaint_type_other" type="text"></label>

                                <h3>Statement of Complaint</h3>
                                <textarea name="statement_details" rows="5"><?php echo htmlspecialchars($row['description']); ?></textarea>

                                <h3>Requested Action</h3>
                                <div class="blotter-checks">
                                    <label><input type="checkbox" name="requested_actions[]" value="Record this incident in the barangay blotter" checked> Record this incident in the barangay blotter</label>
                                    <label><input type="checkbox" name="requested_actions[]" value="Summon the respondent for mediation"> Summon respondent for mediation</label>
                                    <label><input type="checkbox" name="requested_actions[]" value="Assist both parties in settling the matter peacefully"> Assist peaceful settlement</label>
                                    <label><input type="checkbox" name="requested_actions[]" value="Issue a certification if needed"> Issue certification if needed</label>
                                    <label><input type="checkbox" name="requested_actions[]" value="Other"> Other</label>
                                </div>
                                <label>Other Action<input name="other_action" type="text"></label>

                                <h3>Witness Information</h3>
                                <div class="blotter-grid">
                                    <label>Name of Witness<input name="witness_name" type="text"></label>
                                    <label>Address<input name="witness_address" type="text"></label>
                                    <label>Contact Number<input name="witness_contact" type="text"></label>
                                </div>
                                <label>Statement of Witness<textarea name="witness_statement" rows="3"></textarea></label>

                                <h3>Barangay Action</h3>
                                <div class="blotter-grid">
                                    <label>Date of Action<input name="action_date" type="date"></label>
                                    <label>Remarks<input name="action_remarks" type="text"></label>
                                    <label>Recorded By<input name="recorded_by" type="text"></label>
                                    <label>Position<input name="recorded_position" type="text" value="Barangay Secretary / Desk Officer"></label>
                                    <label>Issued Day
                                        <select name="issued_day">
                                            <option value="">Day</option>
                                            <?php for($day = 1; $day <= 31; $day++): ?>
                                                <option value="<?php echo $day; ?>" <?php echo intval(date('j')) === $day ? 'selected' : ''; ?>><?php echo $day; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </label>
                                    <label>Issued Month
                                        <select name="issued_month">
                                            <?php foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $month): ?>
                                                <option value="<?php echo $month; ?>" <?php echo date('F') === $month ? 'selected' : ''; ?>><?php echo $month; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label>Issued Year
                                        <select name="issued_year_suffix">
                                            <?php for($year = intval(date('Y')) - 1; $year <= intval(date('Y')) + 5; $year++): ?>
                                                <option value="<?php echo substr((string)$year, -2); ?>" <?php echo intval(date('Y')) === $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </label>
                                    <label>Prepared By<input name="prepared_by" type="text" value="Barangay Secretary / Desk Officer"></label>
                                    <label>Approved By<input name="approved_by" type="text" value="Punong Barangay"></label>
                                </div>

                                <div class="modal-actions">
                                    <button type="submit">Generate and Attach PDF</button>
                                    <button type="button" class="secondary-action" onclick="this.closest('dialog').close()">Cancel</button>
                                </div>
                            </form>
                        </dialog>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php render_pagination($pagination, 'complaints'); ?>
</div>

<script>
document.querySelectorAll('.respondent-account-select').forEach(function(select) {
    select.addEventListener('change', function() {
        const option = select.options[select.selectedIndex];
        const form = select.closest('form');

        if (!form || !option) {
            return;
        }

        const values = {
            name: option.dataset.name || '',
            age: option.dataset.age || '',
            gender: option.dataset.gender || '',
            civilStatus: option.dataset.civilStatus || '',
            address: option.dataset.address || '',
            contact: option.dataset.contact || ''
        };

        Object.keys(values).forEach(function(key) {
            const field = form.querySelector('[data-respondent-field="' + key + '"]');
            if (field) {
                field.value = values[key];
            }
        });
    });
});
</script>

<?php include('../includes/footer.php'); ?>

