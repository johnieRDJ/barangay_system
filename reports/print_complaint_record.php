<?php
session_start();

if(!headers_sent()){
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

if(
    !isset($_SESSION['user_id'], $_SESSION['role']) ||
    !in_array($_SESSION['role'], ['admin', 'staff'], true)
){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');

$user_id = intval($_SESSION['user_id']);
$role = $_SESSION['role'];
$complaint_id = intval($_GET['id'] ?? 0);
$complaint = null;
$timeline = [];

if($complaint_id > 0){
    $types = 'i';
    $params = [$complaint_id];
    $accessCondition = "";

    if($role === 'staff'){
        $accessCondition = "AND complaints.assigned_staff_id=?";
        $types .= 'i';
        $params[] = $user_id;
    }

    $complaint = db_select_one($conn,
    "SELECT complaints.complaint_id,
            complaints.tracking_number,
            complaints.subject,
            complaints.description,
            complaints.status,
            complaints.resolution_confirmation,
            complaints.staff_comment,
            complaints.created_at,
            complaints.assigned_staff_id,
            complainant.firstname AS complainant_firstname,
            complainant.lastname AS complainant_lastname,
            complainant.email AS complainant_email,
            complainant_profile.address AS complainant_address,
            complainant_profile.phone AS complainant_phone,
            staff.firstname AS staff_firstname,
            staff.lastname AS staff_lastname,
            staff.email AS staff_email
     FROM complaints
     INNER JOIN users complainant ON complaints.complainant_id = complainant.user_id
     LEFT JOIN user_profiles complainant_profile ON complainant.user_id = complainant_profile.user_id
     LEFT JOIN users staff ON complaints.assigned_staff_id = staff.user_id
     WHERE complaints.complaint_id=?
     $accessCondition
     LIMIT 1",
     $types,
     $params);

    if($complaint){
        $timeline = db_select_all($conn,
        "SELECT complaint_updates.*,
                users.firstname,
                users.lastname
         FROM complaint_updates
         LEFT JOIN users ON complaint_updates.actor_user_id = users.user_id
         WHERE complaint_updates.complaint_id=?
         ORDER BY complaint_updates.created_at ASC, complaint_updates.update_id ASC",
         'i',
         [$complaint_id]);

        $updateIds = array_map('intval', array_column($timeline, 'update_id'));
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

        db_execute($conn,
        "INSERT INTO logs (user_id, action)
         VALUES (?, ?)",
         'is',
         [$user_id, "Generated printable complaint record for complaint ID $complaint_id"]);
    }
}

$preparedBy = db_select_one($conn,
"SELECT firstname, lastname FROM users WHERE user_id=? LIMIT 1",
'i',
[$user_id]);
$preparedByName = $preparedBy ? trim($preparedBy['firstname'] . ' ' . $preparedBy['lastname']) : ucfirst($role);

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<div class="page-shell record-page">
    <div class="dashboard-header no-print">
        <h1>Printable Complaint Record</h1>
        <p>Generate an official barangay record form for filing, review, or documentation.</p>
    </div>

    <?php if(!$complaint): ?>
        <div class="table-card">
            <p style="margin:0; color:#b91c1c; font-weight:700;">Complaint record not found or you do not have access to it.</p>
            <p class="developer-note" style="margin-top:8px;">Staff can only print records for complaints assigned to them.</p>
        </div>
    <?php else: ?>
        <?php
        $complainantName = trim($complaint['complainant_firstname'] . ' ' . $complaint['complainant_lastname']);
        $staffName = trim(($complaint['staff_firstname'] ?? '') . ' ' . ($complaint['staff_lastname'] ?? ''));
        $submittedAt = date('F j, Y g:i A', strtotime($complaint['created_at']));
        $generatedAt = date('F j, Y g:i A');
        ?>

        <div class="ticket-actions no-print">
            <button type="button" onclick="window.print()">Print Record Form</button>
            <?php if($role === 'admin'): ?>
                <a href="../admin/manage_complaints.php" class="page-action secondary-action">Back to Manage Complaints</a>
            <?php else: ?>
                <a href="../staff/view_complaints.php" class="page-action secondary-action">Back to Assigned Complaints</a>
            <?php endif; ?>
        </div>

        <article class="record-sheet">
            <header class="record-header">
                <div>
                    <p class="ticket-kicker">Barangay Digital Complaint Desk System</p>
                    <h2>Official Complaint Record Form</h2>
                    <p>For barangay staff/admin documentation and filing.</p>
                </div>
                <div class="ticket-number-box">
                    <span>Tracking Number</span>
                    <strong><?php echo htmlspecialchars($complaint['tracking_number']); ?></strong>
                </div>
            </header>

            <section class="record-section">
                <h3>Record Summary</h3>
                <div class="ticket-grid">
                    <div>
                        <span>Complaint ID</span>
                        <strong><?php echo intval($complaint['complaint_id']); ?></strong>
                    </div>
                    <div>
                        <span>Date Submitted</span>
                        <strong><?php echo htmlspecialchars($submittedAt); ?></strong>
                    </div>
                    <div>
                        <span>Current Status</span>
                        <strong><?php echo htmlspecialchars($complaint['status']); ?></strong>
                    </div>
                    <div>
                        <span>Generated On</span>
                        <strong><?php echo htmlspecialchars($generatedAt); ?></strong>
                    </div>
                </div>
            </section>

            <section class="record-section">
                <h3>Complainant Information</h3>
                <div class="ticket-grid">
                    <div>
                        <span>Name</span>
                        <strong><?php echo htmlspecialchars($complainantName); ?></strong>
                    </div>
                    <div>
                        <span>Email</span>
                        <strong><?php echo htmlspecialchars($complaint['complainant_email']); ?></strong>
                    </div>
                    <div>
                        <span>Phone</span>
                        <strong><?php echo htmlspecialchars($complaint['complainant_phone'] ?: 'N/A'); ?></strong>
                    </div>
                    <div>
                        <span>Address</span>
                        <strong><?php echo htmlspecialchars($complaint['complainant_address'] ?: 'N/A'); ?></strong>
                    </div>
                </div>
            </section>

            <section class="record-section">
                <h3>Complaint Details</h3>
                <div class="ticket-block">
                    <span>Subject</span>
                    <strong><?php echo htmlspecialchars($complaint['subject']); ?></strong>
                </div>
                <div class="ticket-block">
                    <span>Description</span>
                    <p><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>
                </div>
                <div class="ticket-block">
                    <span>Latest Staff Remarks</span>
                    <p><?php echo !empty($complaint['staff_comment']) ? nl2br(htmlspecialchars($complaint['staff_comment'])) : 'No staff remarks yet.'; ?></p>
                </div>
            </section>

            <section class="record-section">
                <h3>Assignment</h3>
                <div class="ticket-grid">
                    <div>
                        <span>Assigned Staff</span>
                        <strong><?php echo $staffName !== '' ? htmlspecialchars($staffName) : 'Not assigned yet'; ?></strong>
                    </div>
                    <div>
                        <span>Staff Email</span>
                        <strong><?php echo !empty($complaint['staff_email']) ? htmlspecialchars($complaint['staff_email']) : 'N/A'; ?></strong>
                    </div>
                </div>
            </section>

            <section class="record-section">
                <h3>Progress Timeline</h3>
                <?php if(empty($timeline)): ?>
                    <p class="record-empty">No timeline updates recorded yet.</p>
                <?php else: ?>
                    <div class="record-timeline">
                        <?php foreach($timeline as $update): ?>
                            <?php
                            $actorName = trim(($update['firstname'] ?? '') . ' ' . ($update['lastname'] ?? ''));
                            $actorLabel = $actorName !== '' ? $actorName : ucfirst($update['actor_role']);
                            $attachments = $attachmentsByUpdate[intval($update['update_id'])] ?? [];
                            ?>
                            <div class="record-timeline-item">
                                <div class="record-timeline-head">
                                    <strong><?php echo htmlspecialchars($update['status_snapshot']); ?></strong>
                                    <span><?php echo date('F j, Y g:i A', strtotime($update['created_at'])); ?></span>
                                </div>
                                <p class="timeline-item-meta">Updated by <?php echo htmlspecialchars($actorLabel); ?> | Type: <?php echo htmlspecialchars($update['update_type']); ?></p>
                                <p><?php echo nl2br(htmlspecialchars($update['message'])); ?></p>
                                <?php if(!empty($attachments)): ?>
                                    <div class="record-proof">
                                        <strong>Proof attachments:</strong>
                                        <?php foreach($attachments as $attachment): ?>
                                            <a href="../view_proof.php?attachment_id=<?php echo intval($attachment['attachment_id']); ?>">
                                                <?php echo htmlspecialchars($attachment['original_name'] ?: basename($attachment['stored_path'])); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="record-section">
                <h3>Prepared For Filing</h3>
                <div class="ticket-grid">
                    <div>
                        <span>Prepared By</span>
                        <strong><?php echo htmlspecialchars($preparedByName); ?></strong>
                    </div>
                    <div>
                        <span>Role</span>
                        <strong><?php echo htmlspecialchars(ucfirst($role)); ?></strong>
                    </div>
                </div>
            </section>

            <footer class="ticket-signature-row">
                <div>
                    <span>Prepared By Signature</span>
                    <strong>&nbsp;</strong>
                </div>
                <div>
                    <span>Reviewed / Approved By</span>
                    <strong>&nbsp;</strong>
                </div>
            </footer>
        </article>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
