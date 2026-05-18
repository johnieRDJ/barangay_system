<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'complainant'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/pagination.php');
include('../includes/complaint_updates.php');
include('../includes/send_complaint_update.php');

$user_id = intval($_SESSION['user_id']);
$action_error = '';

if(isset($_POST['complaint_action'])){
    $complaintId = intval($_POST['complaint_id']);
    $complaintAction = $_POST['complaint_action'];
    $reopenNote = trim($_POST['reopen_note'] ?? '');
    $postedPerPage = intval($_POST['per_page'] ?? 10);
    $postedPerPage = in_array($postedPerPage, [10, 20, 30, 40, 50], true) ? $postedPerPage : 10;
    $postedStatusFilter = $_POST['status_filter'] ?? '';
    $postedStatusFilter = in_array($postedStatusFilter, ['Pending', 'In Progress', 'Awaiting Confirmation', 'Resolved', 'Cancelled'], true) ? $postedStatusFilter : '';
    $redirectBase = [
        'page' => max(1, intval($_POST['page'] ?? 1)),
        'per_page' => $postedPerPage,
    ];

    if($postedStatusFilter !== ''){
        $redirectBase['status'] = $postedStatusFilter;
    }

    $complaint = db_select_one($conn,
    "SELECT complaints.complaint_id,
            complaints.tracking_number,
            complaints.subject,
            complaints.status,
            complaints.resolution_confirmation,
            staff.email AS staff_email,
            staff.firstname AS staff_firstname,
            staff.lastname AS staff_lastname
     FROM complaints
     LEFT JOIN users staff ON complaints.assigned_staff_id = staff.user_id
     WHERE complaint_id=?
     AND complainant_id=?
     LIMIT 1",
     'ii',
     [$complaintId, $user_id]);

    if(!$complaint){
        $action_error = 'Complaint not found.';
    } elseif($complaint['status'] !== 'Resolved' || $complaint['resolution_confirmation'] !== 'pending'){
        $action_error = 'This complaint is not waiting for your confirmation.';
    } elseif($complaintAction === 'confirm'){
        db_execute($conn,
        "UPDATE complaints
         SET resolution_confirmation='confirmed'
         WHERE complaint_id=?
         AND complainant_id=?",
         'ii',
         [$complaintId, $user_id]);

        addComplaintUpdate(
            $conn,
            $complaintId,
            $user_id,
            'complainant',
            'resolution_confirmed',
            'Resolved',
            'Complainant confirmed that the complaint has been resolved.'
        );

        db_execute($conn,
        "INSERT INTO logs (user_id, action)
         VALUES (?, ?)",
         'is',
         [$user_id, "Confirmed resolution for complaint ID $complaintId"]);

        if(!empty($complaint['staff_email'])){
            $staffName = trim($complaint['staff_firstname'] . ' ' . $complaint['staff_lastname']);
            sendComplaintTimelineUpdate(
                $complaint['staff_email'],
                $staffName,
                $complaint['subject'],
                $complaint['tracking_number'],
                'Resolved',
                'The complainant confirmed that the complaint has been resolved.',
                'Complainant',
                rtrim(defined('APP_URL') ? APP_URL : 'http://localhost/barangay', '/') . '/staff/view_complaints.php'
            );
        }

        header("Location: my_complaints.php?" . http_build_query(array_merge($redirectBase, ['confirmation' => 'confirmed'])));
        exit();
    } elseif($complaintAction === 'reopen'){
        if($reopenNote === ''){
            $action_error = 'Please tell the staff why the complaint is not yet resolved.';
        } else {
            db_execute($conn,
            "UPDATE complaints
             SET status='In Progress',
                 resolution_confirmation='reopened'
             WHERE complaint_id=?
             AND complainant_id=?",
             'ii',
             [$complaintId, $user_id]);

            addComplaintUpdate(
                $conn,
                $complaintId,
                $user_id,
                'complainant',
                'resolution_reopened',
                'In Progress',
                "Complainant marked the complaint as not yet resolved. Reason: $reopenNote"
            );

            db_execute($conn,
            "INSERT INTO logs (user_id, action)
             VALUES (?, ?)",
             'is',
             [$user_id, "Reopened complaint ID $complaintId with feedback: $reopenNote"]);

            if(!empty($complaint['staff_email'])){
                $staffName = trim($complaint['staff_firstname'] . ' ' . $complaint['staff_lastname']);
                sendComplaintTimelineUpdate(
                    $complaint['staff_email'],
                    $staffName,
                    $complaint['subject'],
                    $complaint['tracking_number'],
                    'In Progress - Reopened',
                    "The complainant marked the complaint as not yet resolved.\n\nReason: $reopenNote",
                    'Complainant',
                    rtrim(defined('APP_URL') ? APP_URL : 'http://localhost/barangay', '/') . '/staff/view_complaints.php'
                );
            }

            header("Location: my_complaints.php?" . http_build_query(array_merge($redirectBase, ['confirmation' => 'reopened'])));
            exit();
        }
    }
}

include('../includes/header.php');
include('../includes/sidebar.php');

$statusFilter = $_GET['status'] ?? '';
$allowedStatusFilters = ['Pending', 'In Progress', 'Awaiting Confirmation', 'Resolved', 'Cancelled'];
$statusTabs = [
    '' => 'All',
    'Pending' => 'Pending',
    'In Progress' => 'In Progress',
    'Awaiting Confirmation' => 'Awaiting',
    'Resolved' => 'Resolved',
    'Cancelled' => 'Cancelled',
];

if(!in_array($statusFilter, $allowedStatusFilters, true)){
    $statusFilter = '';
}

$whereSql = "complaints.complainant_id=?";
$types = 'i';
$params = [$user_id];

if($statusFilter === 'Awaiting Confirmation'){
    $whereSql .= " AND complaints.status='Resolved' AND complaints.resolution_confirmation='pending'";
} elseif($statusFilter === 'Resolved'){
    $whereSql .= " AND complaints.status='Resolved' AND (complaints.resolution_confirmation='confirmed' OR complaints.resolution_confirmation IS NULL)";
} elseif($statusFilter !== ''){
    $whereSql .= " AND complaints.status=?";
    $types .= 's';
    $params[] = $statusFilter;
}

$statusOrderSql = "CASE
    WHEN complaints.status='Pending' THEN 1
    WHEN complaints.status='In Progress' THEN 2
    WHEN complaints.status='Resolved' AND complaints.resolution_confirmation='pending' THEN 3
    WHEN complaints.status='Resolved' THEN 4
    WHEN complaints.status='Cancelled' THEN 5
    ELSE 6
 END";

$pagination = pagination_state($conn,
"SELECT COUNT(*) AS total
 FROM complaints
 LEFT JOIN users u ON complaints.assigned_staff_id = u.user_id
 WHERE $whereSql",
 $types,
 $params);

$complaints = db_select_all($conn,
"SELECT complaints.*, u.firstname AS staff_firstname, u.lastname AS staff_lastname
 FROM complaints
 LEFT JOIN users u ON complaints.assigned_staff_id = u.user_id
 WHERE $whereSql
 ORDER BY $statusOrderSql, complaints.complaint_id DESC" . $pagination['limit_sql'],
 $types,
 $params);

$complaintIds = array_map('intval', array_column($complaints, 'complaint_id'));
$timelineRows = [];

if(!empty($complaintIds)){
    $placeholders = implode(',', array_fill(0, count($complaintIds), '?'));
    $timelineRows = db_select_all($conn,
    "SELECT complaint_updates.*, users.firstname, users.lastname
     FROM complaint_updates
     LEFT JOIN users ON complaint_updates.actor_user_id = users.user_id
     WHERE complaint_updates.complaint_id IN ($placeholders)
     ORDER BY complaint_updates.created_at DESC, complaint_updates.update_id DESC",
     str_repeat('i', count($complaintIds)),
     $complaintIds);
}

$timelineByComplaint = [];
$updateIds = array_map('intval', array_column($timelineRows, 'update_id'));
$attachmentsByUpdate = [];

if(!empty($updateIds)){
    $placeholders = implode(',', array_fill(0, count($updateIds), '?'));
    $attachmentRows = db_select_all($conn,
    "SELECT complaint_update_attachments.*
     FROM complaint_update_attachments
     WHERE complaint_update_attachments.update_id IN ($placeholders)
     ORDER BY complaint_update_attachments.attachment_id ASC",
     str_repeat('i', count($updateIds)),
     $updateIds);

    foreach($attachmentRows as $attachmentRow){
        $attachmentsByUpdate[intval($attachmentRow['update_id'])][] = $attachmentRow;
    }
}

foreach($timelineRows as $timelineRow){
    $timelineByComplaint[$timelineRow['complaint_id']][] = $timelineRow;
}
?>

<div class="page-shell">
    <div class="dashboard-header">
        <h1>My Complaints</h1>
        <p>Track every action on your complaint, including who handled it and when updates were recorded.</p>
    </div>

    <?php if($action_error !== ''): ?>
        <div class="table-card">
            <p style="margin:0; color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($action_error); ?></p>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['confirmation']) && $_GET['confirmation'] === 'confirmed'): ?>
        <div class="table-card">
            <p style="margin:0; color:#15803d; font-weight:600;">You confirmed that the complaint has been resolved.</p>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['confirmation']) && $_GET['confirmation'] === 'reopened'): ?>
        <div class="table-card">
            <p style="margin:0; color:#b45309; font-weight:600;">The complaint was returned to staff for more action.</p>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['cancelled'])): ?>
        <div class="table-card">
            <p style="margin:0; color:#4b5563; font-weight:600;">The complaint was cancelled and kept in your complaint history.</p>
        </div>
    <?php endif; ?>

    <nav class="status-tabs" aria-label="Complaint status filters">
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
            <a href="my_complaints.php?<?php echo htmlspecialchars(http_build_query($tabQuery)); ?>" class="<?php echo $isActiveTab ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($tabLabel); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="complaint-list">
        <?php if(count($complaints) === 0): ?>
            <div class="table-card">
                <p style="margin:0; color:#5b6b7f;"><?php echo $statusFilter !== '' ? 'No complaints match this filter.' : 'You have not submitted any complaints yet.'; ?></p>
            </div>
        <?php endif; ?>

        <?php foreach($complaints as $row): ?>
            <?php
            $complaintId = intval($row['complaint_id']);
            $timeline = $timelineByComplaint[$complaintId] ?? [];
            $assignedStaff = trim(($row['staff_firstname'] ?? '') . ' ' . ($row['staff_lastname'] ?? ''));
            $isAwaitingConfirmation = $row['status'] === 'Resolved' && $row['resolution_confirmation'] === 'pending';
            $latestTimeline = $timeline[0] ?? null;
            $latestUpdateText = !empty($row['staff_comment'])
                ? $row['staff_comment']
                : ($latestTimeline['message'] ?? 'No update yet');
            $latestUpdatePlain = trim(strip_tags($latestUpdateText));
            $latestUpdateExcerpt = strlen($latestUpdatePlain) > 140
                ? substr($latestUpdatePlain, 0, 140) . '...'
                : $latestUpdatePlain;
            ?>

            <div class="table-card complaint-card">
                <div class="complaint-card-header">
                    <div>
                        <h2 style="text-align:left; margin-bottom:6px;"><?php echo htmlspecialchars($row['subject']); ?></h2>
                        <p class="tracking-number">Tracking No. <?php echo htmlspecialchars($row['tracking_number']); ?></p>
                        <p class="developer-note" style="margin-bottom:0;">Submitted on <?php echo date('F j, Y g:i A', strtotime($row['created_at'])); ?></p>
                    </div>

                    <div class="complaint-status-group">
                        <?php if($row['status'] === 'Pending'): ?>
                            <span class="status-badge status-pending">Pending</span>
                        <?php elseif($row['status'] === 'Resolved' && $row['resolution_confirmation'] === 'pending'): ?>
                            <span class="status-badge complaint-status-awaiting">Awaiting Your Confirmation</span>
                        <?php elseif($row['status'] === 'In Progress' && $row['resolution_confirmation'] === 'reopened'): ?>
                            <span class="status-badge complaint-status-reopened">Reopened</span>
                        <?php elseif($row['status'] === 'In Progress'): ?>
                            <span class="status-badge complaint-status-progress">In Progress</span>
                        <?php elseif($row['status'] === 'Cancelled'): ?>
                            <span class="status-badge status-cancelled">Cancelled</span>
                        <?php else: ?>
                            <span class="status-badge status-approved">Resolved</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="complaint-card-snapshot">
                    <p><strong>Assigned Staff:</strong> <?php echo $assignedStaff !== '' ? htmlspecialchars($assignedStaff) : '<span class="table-muted">Not assigned yet</span>'; ?></p>
                    <p><strong>Latest Update:</strong> <?php echo htmlspecialchars($latestUpdateExcerpt); ?></p>
                </div>

                <details class="complaint-details-toggle" <?php echo $isAwaitingConfirmation ? 'open' : ''; ?>>
                    <summary>
                        <span class="summary-open-label"><?php echo $isAwaitingConfirmation ? 'Review Complaint' : 'View Details'; ?></span>
                        <span class="summary-close-label"><?php echo $isAwaitingConfirmation ? 'Hide Review' : 'Hide Details'; ?></span>
                    </summary>

                    <div class="complaint-detail-grid">
                        <div>
                            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                            <p><strong>Assigned Staff:</strong> <?php echo $assignedStaff !== '' ? htmlspecialchars($assignedStaff) : '<span class="table-muted">Not assigned yet</span>'; ?></p>
                            <p><strong>Latest Staff Update:</strong> <?php echo !empty($row['staff_comment']) ? nl2br(htmlspecialchars($row['staff_comment'])) : '<span class="table-muted">No update yet</span>'; ?></p>
                        </div>

                        <div class="complaint-action-links">
                            <?php if($row['status'] === 'Pending'): ?>
                                <a href="print_ticket.php?id=<?php echo $complaintId; ?>" class="page-action">Print Complaint</a>
                                <a href="edit_complaint.php?id=<?php echo $complaintId; ?>" class="page-action secondary-action">Edit Complaint</a>
                                <a href="delete_complaints.php?id=<?php echo $complaintId; ?>&page=<?php echo intval($pagination['page']); ?>&per_page=<?php echo intval($pagination['per_page']); ?>&status=<?php echo urlencode($statusFilter); ?>" class="page-action secondary-action" onclick="return confirm('Cancel this complaint? It will stay in your history.');">Cancel Complaint</a>
                            <?php elseif($row['status'] === 'Resolved' && $row['resolution_confirmation'] === 'pending'): ?>
                                <a href="print_ticket.php?id=<?php echo $complaintId; ?>" class="page-action">Print Complaint</a>
                                <form method="POST" class="complaint-confirmation-form">
                                    <input type="hidden" name="complaint_id" value="<?php echo $complaintId; ?>">
                                    <input type="hidden" name="page" value="<?php echo intval($pagination['page']); ?>">
                                    <input type="hidden" name="per_page" value="<?php echo intval($pagination['per_page']); ?>">
                                    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($statusFilter); ?>">
                                    <textarea name="reopen_note" placeholder="If not yet resolved, explain what is still needed."></textarea>
                                    <button type="submit" name="complaint_action" value="confirm">Confirm Resolved</button>
                                    <button type="submit" name="complaint_action" value="reopen" class="secondary-action">Not Yet Resolved</button>
                                </form>
                            <?php elseif($row['status'] === 'Resolved' && $row['resolution_confirmation'] === 'confirmed'): ?>
                                <a href="print_ticket.php?id=<?php echo $complaintId; ?>" class="page-action">Print Complaint</a>
                                <span class="table-muted">You already confirmed that this complaint was resolved.</span>
                            <?php elseif($row['status'] === 'In Progress' && $row['resolution_confirmation'] === 'reopened'): ?>
                                <a href="print_ticket.php?id=<?php echo $complaintId; ?>" class="page-action">Print Complaint</a>
                                <span class="table-muted">You sent this complaint back to staff for more action.</span>
                            <?php else: ?>
                                <a href="print_ticket.php?id=<?php echo $complaintId; ?>" class="page-action">Print Complaint</a>
                                <span class="table-muted">Editing is disabled once work has started.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="complaint-timeline">
                        <h3>Progress Timeline</h3>

                        <?php if(empty($timeline)): ?>
                            <p class="table-muted">No activity has been recorded yet.</p>
                        <?php else: ?>
                            <?php foreach($timeline as $update): ?>
                                <?php
                                $actorName = trim(($update['firstname'] ?? '') . ' ' . ($update['lastname'] ?? ''));
                                $actorLabel = $actorName !== '' ? $actorName : ucfirst($update['actor_role']);
                                $attachments = $attachmentsByUpdate[intval($update['update_id'])] ?? [];
                                ?>

                                <div class="timeline-item">
                                    <div class="timeline-item-header">
                                        <strong><?php echo htmlspecialchars($update['status_snapshot']); ?></strong>
                                        <span><?php echo date('F j, Y g:i A', strtotime($update['created_at'])); ?></span>
                                    </div>
                                    <p class="timeline-item-meta">Updated by <?php echo htmlspecialchars($actorLabel); ?></p>
                                    <p><?php echo nl2br(htmlspecialchars($update['message'])); ?></p>
                                    <?php if(!empty($attachments)): ?>
                                        <div class="timeline-attachments">
                                            <?php foreach($attachments as $attachment): ?>
                                                <?php
                                                $attachmentId = intval($attachment['attachment_id']);
                                                $fileName = $attachment['original_name'] ?: basename($attachment['stored_path']);
                                                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png'], true);
                                                $isVideo = in_array($extension, ['mp4', 'mov', 'webm'], true);
                                                ?>
                                                <a class="proof-attachment <?php echo $isImage ? 'proof-attachment-image' : ''; ?>" href="../view_proof.php?attachment_id=<?php echo $attachmentId; ?>">
                                                    <?php if($isImage): ?>
                                                        <img src="../view_proof.php?attachment_id=<?php echo $attachmentId; ?>&raw=1" alt="<?php echo htmlspecialchars($fileName); ?>">
                                                    <?php elseif($isVideo): ?>
                                                        <video src="../view_proof.php?attachment_id=<?php echo $attachmentId; ?>&raw=1" muted preload="metadata"></video>
                                                    <?php elseif($extension === 'pdf'): ?>
                                                        <span class="proof-attachment-icon">PDF</span>
                                                    <?php else: ?>
                                                        <span class="proof-attachment-icon">FILE</span>
                                                    <?php endif; ?>
                                                    <span><?php echo htmlspecialchars($fileName); ?></span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </details>
            </div>
        <?php endforeach; ?>
    </div>
    <?php render_pagination($pagination, 'complaints'); ?>
</div>

<?php include('../includes/footer.php'); ?>
