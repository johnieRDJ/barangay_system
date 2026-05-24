<?php
session_start();

include('../config/database.php');
include('../includes/pagination.php');
include('../includes/complaint_updates.php');
include('../includes/blotter_pdf.php');


include('../includes/send_complaint_update.php');
require_once __DIR__ . '/../includes/notifications.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

// ============================
//  HANDLE ASSIGN / REASSIGN
// ============================
if(isset($_POST['assign'])){

    $complaint_id = intval($_POST['complaint_id']);
    $staff_id = intval($_POST['staff_id']);
    $postedStatusFilter = $_POST['status_filter'] ?? '';
    $postedStaffFilter = $_POST['staff_filter'] ?? '';
    $postedSearch = trim($_POST['search'] ?? '');
    $postedPerPage = intval($_POST['per_page'] ?? 10);
    $postedPerPage = in_array($postedPerPage, [10, 20, 30, 40, 50], true) ? $postedPerPage : 10;
    $redirectQuery = http_build_query([
        'page' => max(1, intval($_POST['page'] ?? 1)),
        'per_page' => $postedPerPage,
        'status' => $postedStatusFilter,
        'staff' => $postedStaffFilter,
        'search' => $postedSearch,
    ]);
    $redirectUrl = 'manage_complaints.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '');

    // Check if already assigned
    $check = db_select_one($conn,
    "SELECT complaints.assigned_staff_id,
            complaints.complainant_id,
            complaints.status,
            complaints.tracking_number,
            complaints.subject,
            users.email,
            users.firstname,
            users.lastname,
            residency.status AS residency_status
     FROM complaints
     JOIN users ON complaints.complainant_id = users.user_id
     LEFT JOIN residency ON complaints.complainant_id = residency.user_id
     WHERE complaints.complaint_id=?
     LIMIT 1",
     'i',
     [$complaint_id]);

    if(!$check){
        header("Location: $redirectUrl");
        exit();
    }

    if($check['status'] === 'Cancelled'){
        header("Location: $redirectUrl");
        exit();
    }

    if(($check['residency_status'] ?? '') !== 'verified'){
        $redirectUrl .= (strpos($redirectUrl, '?') === false ? '?' : '&') . 'residency_required=1';
        header("Location: $redirectUrl");
        exit();
    }

    if($check['assigned_staff_id']){
        $log_msg = "Updated staff assignment for complaint ID $complaint_id";
    } else {
        $log_msg = "Assigned staff to complaint ID $complaint_id";
    }

    $staff_data = db_select_one($conn,
    "SELECT firstname, lastname, email
     FROM users
     WHERE user_id=?
     AND role='staff'
     AND account_status='approved'
     LIMIT 1",
     'i',
     [$staff_id]);

    $staff_name = $staff_data
        ? trim($staff_data['firstname'] . ' ' . $staff_data['lastname'])
        : 'selected staff member';

    // Update complaint
    if($staff_data){
        if(intval($check['assigned_staff_id']) === $staff_id && $check['status'] === 'In Progress'){
            header("Location: $redirectUrl");
            exit();
        }

        $assignStmt = db_prepared_query($conn,
    "UPDATE complaints
     SET assigned_staff_id=?,
         status='In Progress',
         resolution_confirmation=NULL
     WHERE complaint_id=?
     AND (
         assigned_staff_id IS NULL
         OR assigned_staff_id<>?
         OR status<>'In Progress'
     )",
     'iii',
     [$staff_id, $complaint_id, $staff_id]);

    $assignmentChanged = $assignStmt ? mysqli_stmt_affected_rows($assignStmt) : 0;
    if($assignStmt){
        mysqli_stmt_close($assignStmt);
    }

    if($assignmentChanged <= 0){
        header("Location: $redirectUrl");
        exit();
    }

    // Save log
    db_execute($conn,
    "INSERT INTO logs (user_id, action)
     VALUES (?, ?)",
     'is',
     [intval($_SESSION['user_id']), $log_msg]);

    addComplaintUpdate(
        $conn,
        $complaint_id,
        intval($_SESSION['user_id']),
        'admin',
        'assigned',
        'In Progress',
        "Complaint assigned to $staff_name."
    );

    $fullname = trim($check['firstname'] . ' ' . $check['lastname']);
    sendComplaintTimelineUpdate(
        $check['email'],
        $fullname,
        $check['subject'],
        $check['tracking_number'],
        'In Progress',
        "Your complaint has been assigned to $staff_name for action.",
        'Barangay Admin'
    );

    notify_user(
        $conn,
        intval($check['complainant_id'] ?? 0),
        'Staff Assigned',
        'The Punong Barangay has assigned ' . $staff_name . ' to handle your complaint.',
        '../complainant/my_complaints.php?status=In+Progress#complaint-' . $complaint_id
    );

    if(!empty($staff_data['email'])){
        sendComplaintTimelineUpdate(
            $staff_data['email'],
            $staff_name,
            $check['subject'],
            $check['tracking_number'],
            'Assigned to You',
            "A complaint has been assigned to you. Please review the complaint details and add progress updates when action is taken.",
            'Barangay Admin',
            rtrim(defined('APP_URL') ? APP_URL : 'http://localhost/barangay', '/') . '/staff/view_complaints.php'
        );

        notify_user(
            $conn,
            $staff_id,
            'Complaint Assigned to You',
            'A complaint has been assigned to you. Please review the details and add progress updates.',
            '../staff/view_complaints.php?status=In+Progress'
        );
    }
    }

    $redirectUrl .= (strpos($redirectUrl, '?') === false ? '?' : '&') . http_build_query([
        'assigned' => 1,
        'staff_name' => $staff_name,
    ]);
    header("Location: $redirectUrl");
    exit();
}

if(isset($_POST['approve_blotter'])){
    $reportId = intval($_POST['report_id'] ?? 0);
    $complaintId = intval($_POST['complaint_id'] ?? 0);
    $postedPerPage = intval($_POST['per_page'] ?? 10);
    $postedPerPage = in_array($postedPerPage, [10, 20, 30, 40, 50], true) ? $postedPerPage : 10;
    $postedStatusFilter = $_POST['status_filter'] ?? '';
    $postedStaffFilter = $_POST['staff_filter'] ?? '';
    $postedSearch = trim($_POST['search'] ?? '');
    $redirectQuery = http_build_query([
        'blotter_approved' => 1,
        'page' => max(1, intval($_POST['page'] ?? 1)),
        'per_page' => $postedPerPage,
        'status' => $postedStatusFilter,
        'staff' => $postedStaffFilter,
        'search' => $postedSearch,
    ]);
    $redirectUrl = 'manage_complaints.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '');
    $adminId = intval($_SESSION['user_id']);

    $report = db_select_one($conn,
    "SELECT blotter_reports.report_id,
            blotter_reports.complainant_user_id,
            blotter_reports.staff_user_id,
            blotter_reports.report_path,
            blotter_reports.report_original_name,
            complaints.tracking_number,
            complaints.subject,
            complainant.email AS complainant_email,
            complainant.firstname AS complainant_firstname,
            complainant.lastname AS complainant_lastname,
            staff.email AS staff_email,
            staff.firstname AS staff_firstname,
            staff.lastname AS staff_lastname
     FROM blotter_reports
     JOIN complaints ON blotter_reports.complaint_id = complaints.complaint_id
     JOIN users complainant ON complaints.complainant_id = complainant.user_id
     LEFT JOIN users staff ON complaints.assigned_staff_id = staff.user_id
     WHERE blotter_reports.report_id=?
     AND blotter_reports.complaint_id=?
     AND blotter_reports.status='submitted_to_admin'
     LIMIT 1",
     'ii',
     [$reportId, $complaintId]);

    if($report){
        $adminProfile = db_select_one($conn,
        "SELECT signature_image FROM user_profiles WHERE user_id=? LIMIT 1",
        'i',
        [$adminId]);
        $adminSignature = $adminProfile['signature_image'] ?? null;

        db_execute($conn,
        "UPDATE blotter_reports
         SET status='approved',
             admin_user_id=?,
             admin_signature_image=?
         WHERE report_id=?",
         'isi',
         [$adminId, $adminSignature, $reportId]);

        regenerate_blotter_report_pdf($conn, $reportId);

        db_execute($conn,
        "UPDATE complaints
         SET status='Resolved',
             resolution_confirmation='pending'
         WHERE complaint_id=?",
         'i',
         [$complaintId]);

        $updateId = addComplaintUpdate(
            $conn,
            $complaintId,
            $adminId,
            'admin',
            'blotter_approved',
            'Resolved',
            'Admin approved the barangay blotter / complaint report. Awaiting complainant confirmation.'
        );

        if($updateId && !empty($adminSignature)){
            $adminSignaturePath = 'uploads/signatures/' . $adminSignature;
            $absoluteSignaturePath = realpath(__DIR__ . '/../' . $adminSignaturePath);

            if($absoluteSignaturePath !== false){
                addComplaintUpdateAttachment(
                    $conn,
                    $updateId,
                    $adminSignaturePath,
                    'Admin E-Signature',
                    strtolower(pathinfo($adminSignaturePath, PATHINFO_EXTENSION)),
                    filesize($absoluteSignaturePath)
                );
            }
        }

        if($updateId && !empty($report['report_path'])){
            $reportPath = realpath(__DIR__ . '/../' . ltrim($report['report_path'], '/\\'));

            if($reportPath !== false){
                addComplaintUpdateAttachment(
                    $conn,
                    $updateId,
                    $report['report_path'],
                    $report['report_original_name'] ?: 'Approved Barangay Blotter Report.pdf',
                    'pdf',
                    filesize($reportPath)
                );
            }
        }

        db_execute($conn,
        "INSERT INTO logs (user_id, action)
         VALUES (?, ?)",
         'is',
         [$adminId, "Approved blotter report ID $reportId for complaint ID $complaintId"]);

        $appUrl = rtrim(defined('APP_URL') ? APP_URL : 'http://localhost/barangay', '/');
        $complainantName = trim($report['complainant_firstname'] . ' ' . $report['complainant_lastname']);
        sendComplaintTimelineUpdate(
            $report['complainant_email'],
            $complainantName,
            $report['subject'],
            $report['tracking_number'],
            'Approved - Awaiting Your Confirmation',
            "The barangay blotter / complaint report has been approved by admin and now has the required signatures. Please open your complaint timeline and confirm if the issue is resolved.",
            'Barangay Admin',
            $appUrl . '/complainant/my_complaints.php'
        );

        notify_user(
            $conn,
            intval($report['complainant_user_id'] ?? 0),
            'Blotter Approved',
            'Admin approved the barangay blotter report. Please review your complaint and confirm if it is resolved.',
            '../complainant/my_complaints.php?status=Awaiting+Confirmation#complaint-' . $complaintId
        );

        if(!empty($report['staff_email'])){
            $staffName = trim($report['staff_firstname'] . ' ' . $report['staff_lastname']);
            sendComplaintTimelineUpdate(
                $report['staff_email'],
                $staffName,
                $report['subject'],
                $report['tracking_number'],
                'Blotter Approved',
                "Admin approved the barangay blotter / complaint report. The approved PDF is now visible in the complaint timeline.",
                'Barangay Admin',
                $appUrl . '/staff/view_complaints.php'
            );

            notify_user(
                $conn,
                intval($report['staff_user_id'] ?? 0),
                'Blotter Approved',
                'Admin approved the barangay blotter report. The approved PDF is visible in the timeline.',
                '../staff/view_complaints.php?status=Resolved'
            );
        }
    }

    header("Location: $redirectUrl");
    exit();
}

include('../includes/header.php');
include('../includes/sidebar.php');

// ============================
//  GET DATA
// ============================
$statusFilter = $_GET['status'] ?? '';
$allowedStatusFilters = ['Pending', 'In Progress', 'Reopened', 'Awaiting Confirmation', 'Resolved', 'Cancelled'];
$statusFilter = in_array($statusFilter, $allowedStatusFilters, true) ? $statusFilter : '';
$staffFilter = $_GET['staff'] ?? '';
$search = trim($_GET['search'] ?? '');
$whereParts = [];
$whereTypes = '';
$whereParams = [];

if($statusFilter === 'Reopened'){
    $whereParts[] = "complaints.status='In Progress' AND complaints.resolution_confirmation='reopened'";
} elseif($statusFilter === 'Awaiting Confirmation'){
    $whereParts[] = "complaints.status='Resolved' AND complaints.resolution_confirmation='pending'";
} elseif($statusFilter === 'Resolved'){
    $whereParts[] = "complaints.status='Resolved' AND (complaints.resolution_confirmation='confirmed' OR complaints.resolution_confirmation IS NULL)";
} elseif($statusFilter !== ''){
    $whereParts[] = "complaints.status=?";
    $whereTypes .= 's';
    $whereParams[] = $statusFilter;
}

if($staffFilter === 'unassigned'){
    $whereParts[] = "complaints.assigned_staff_id IS NULL";
} elseif($staffFilter !== '' && ctype_digit($staffFilter)){
    $whereParts[] = "complaints.assigned_staff_id=?";
    $whereTypes .= 'i';
    $whereParams[] = intval($staffFilter);
}

if($search !== ''){
    $whereParts[] = "(complaints.tracking_number LIKE ? OR complaints.subject LIKE ? OR complaints.description LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?)";
    $whereTypes .= 'sssss';
    $searchLike = '%' . $search . '%';
    array_push($whereParams, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike);
}

$whereSql = empty($whereParts) ? '' : ' WHERE ' . implode(' AND ', $whereParts);

$pagination = pagination_state($conn,
"SELECT COUNT(*) AS total
 FROM complaints
 JOIN users u ON complaints.complainant_id = u.user_id
 LEFT JOIN users s ON complaints.assigned_staff_id = s.user_id
 $whereSql",
 $whereTypes,
 $whereParams);

$complaints = db_select_all($conn,
"SELECT complaints.*, 
        u.firstname AS fname, u.lastname AS lname, u.email,
        residency.status AS residency_status,
        s.firstname AS staff_fname, s.lastname AS staff_lname
 FROM complaints
 JOIN users u ON complaints.complainant_id = u.user_id
 LEFT JOIN residency ON complaints.complainant_id = residency.user_id
 LEFT JOIN users s ON complaints.assigned_staff_id = s.user_id
 $whereSql
 ORDER BY complaints.complaint_id DESC" . $pagination['limit_sql'],
 $whereTypes,
 $whereParams);

// Only approved staff
$staffRows = db_select_all($conn,
"SELECT * FROM users 
 WHERE role='staff' AND account_status='approved'");

$complaintIds = array_map('intval', array_column($complaints, 'complaint_id'));
$blotterReportsByComplaint = [];

if(!empty($complaintIds)){
    $placeholders = implode(',', array_fill(0, count($complaintIds), '?'));
    $blotterReportRows = db_select_all($conn,
    "SELECT *
     FROM blotter_reports
     WHERE complaint_id IN ($placeholders)
     ORDER BY created_at DESC, report_id DESC",
     str_repeat('i', count($complaintIds)),
     $complaintIds);

    foreach($blotterReportRows as $reportRow){
        $reportComplaintId = intval($reportRow['complaint_id']);

        if(!isset($blotterReportsByComplaint[$reportComplaintId])){
            $blotterReportsByComplaint[$reportComplaintId] = $reportRow;
        }
    }
}
?>

<h2>Manage Complaints</h2>

<?php if(isset($_GET['blotter_approved'])): ?>
    <div class="table-card">
        <p style="margin:0; color:#15803d; font-weight:600;">Blotter report approved and added to the complaint timeline.</p>
    </div>
<?php endif; ?>

<?php if(isset($_GET['assigned'])): ?>
    <div class="table-card">
        <p style="margin:0; color:#15803d; font-weight:600;">
            You have assigned <?php echo htmlspecialchars($_GET['staff_name'] ?? 'the selected staff'); ?> successfully.
        </p>
    </div>
<?php endif; ?>

<?php if(isset($_GET['residency_required'])): ?>
    <div class="table-card">
        <p style="margin:0; color:#b91c1c; font-weight:600;">Cannot assign staff yet. The complainant residency is not verified.</p>
    </div>
<?php endif; ?>

<form method="GET" class="filters-bar complaint-filter-bar">
    <div class="filter-group filter-search">
        <label for="search">Search</label>
        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Tracking, subject, complainant...">
    </div>

    <div class="filter-group">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">All Status</option>
            <?php foreach($allowedStatusFilters as $filterOption): ?>
                <option value="<?php echo htmlspecialchars($filterOption); ?>" <?php echo $statusFilter === $filterOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($filterOption); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-group">
        <label for="staff">Assigned Staff</label>
        <select id="staff" name="staff">
            <option value="">All Staff</option>
            <option value="unassigned" <?php echo $staffFilter === 'unassigned' ? 'selected' : ''; ?>>Unassigned</option>
            <?php foreach($staffRows as $staffOption): ?>
                <?php $staffOptionId = (string)intval($staffOption['user_id']); ?>
                <option value="<?php echo $staffOptionId; ?>" <?php echo $staffFilter === $staffOptionId ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars(trim($staffOption['firstname'] . ' ' . $staffOption['lastname'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <input type="hidden" name="per_page" value="<?php echo intval($pagination['per_page']); ?>">
    <div class="filter-actions">
        <div class="filter-primary-action">
            <button type="submit">Apply Filter</button>
        </div>
        <div class="filter-secondary-actions">
            <a class="page-action secondary-action" href="manage_complaints.php?per_page=<?php echo intval($pagination['per_page']); ?>">Clear Filter</a>
        </div>
    </div>
</form>

<div class="table-card">
<table border="1" cellpadding="10" width="100%" class="responsive-table admin-complaints-table">
<tr>
    <th>Tracking No.</th>
    <th>Complainant</th>
    <th>Subject</th>
    <th>Description</th>
    <th>Status</th>
    <th>Assigned Staff</th>
    <th>Record / Assign</th>
</tr>

<?php foreach($complaints as $row): ?>
<?php $latestBlotterReport = $blotterReportsByComplaint[intval($row['complaint_id'])] ?? null; ?>

<tr>

<td data-label="Tracking No."><span class="tracking-number compact"><?php echo htmlspecialchars($row['tracking_number']); ?></span></td>

<td data-label="Complainant"><?php echo htmlspecialchars($row['fname']." ".$row['lname']); ?></td>

<td data-label="Subject"><?php echo htmlspecialchars($row['subject']); ?></td>

<td data-label="Description"><?php echo htmlspecialchars($row['description']); ?></td>

<td data-label="Status">
<?php
if($row['status'] == 'Pending'){
    echo "<span style='color:orange;'>Pending</span>";
}
elseif($row['status'] == 'Cancelled'){
    echo "<span class='status-badge status-cancelled'>Cancelled</span>";
}
elseif($row['status'] == 'Resolved' && $row['resolution_confirmation'] == 'pending'){
    echo "<span style='color:#1d4f91;'>Awaiting Confirmation</span>";
}
elseif($row['status'] == 'In Progress' && $row['resolution_confirmation'] == 'reopened'){
    echo "<span style='color:#b45309;'>Reopened</span>";
}
elseif($row['status'] == 'In Progress'){
    echo "<span style='color:blue;'>In Progress</span>";
}
else{
    echo "<span style='color:green;'>Resolved</span>";
}
?>
</td>

<td data-label="Assigned Staff">
<?php
if($row['staff_fname']){
    echo htmlspecialchars($row['staff_fname']." ".$row['staff_lname']);
}else{
    echo "<i>Not Assigned</i>";
}
?>
</td>

<td data-label="Record / Assign">

<div class="action-links" style="margin-bottom:10px;">
    <a href="../reports/print_complaint_record.php?id=<?php echo intval($row['complaint_id']); ?>">Print Record</a>
</div>

<?php if($latestBlotterReport): ?>
    <div class="blotter-sign-box">
        <strong>Blotter Report</strong>
        <a class="page-action secondary-action" href="../view_blotter_report.php?report_id=<?php echo intval($latestBlotterReport['report_id']); ?>">Open Blotter PDF</a>
        <?php if($latestBlotterReport['status'] === 'submitted_to_admin'): ?>
            <p class="table-muted">Ready for admin approval.</p>
            <form method="POST">
                <input type="hidden" name="report_id" value="<?php echo intval($latestBlotterReport['report_id']); ?>">
                <input type="hidden" name="complaint_id" value="<?php echo intval($row['complaint_id']); ?>">
                <input type="hidden" name="page" value="<?php echo intval($pagination['page']); ?>">
                <input type="hidden" name="per_page" value="<?php echo intval($pagination['per_page']); ?>">
                <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <input type="hidden" name="staff_filter" value="<?php echo htmlspecialchars($staffFilter); ?>">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" name="approve_blotter">Approve Report</button>
            </form>
        <?php elseif($latestBlotterReport['status'] === 'approved'): ?>
            <p class="table-muted">Approved by admin.</p>
        <?php elseif($latestBlotterReport['status'] === 'signed_by_complainant'): ?>
            <p class="table-muted">Waiting for staff submission.</p>
        <?php elseif($latestBlotterReport['status'] === 'awaiting_complainant_signature'): ?>
            <p class="table-muted">Waiting for complainant signature.</p>
        <?php else: ?>
            <p class="table-muted">Report status: <?php echo htmlspecialchars($latestBlotterReport['status']); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if($row['status'] === 'Cancelled'): ?>
    <span class="table-muted">Cancelled by complainant</span>
<?php elseif(($row['residency_status'] ?? '') !== 'verified'): ?>
    <span class="table-muted">Residency not verified. Staff assignment is disabled.</span>
<?php else: ?>
<form method="POST">

<input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">
<input type="hidden" name="page" value="<?php echo intval($pagination['page']); ?>">
<input type="hidden" name="per_page" value="<?php echo intval($pagination['per_page']); ?>">
<input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($statusFilter); ?>">
<input type="hidden" name="staff_filter" value="<?php echo htmlspecialchars($staffFilter); ?>">
<input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
<select name="staff_id" required>

<?php
foreach($staffRows as $s):
?>

<option value="<?php echo $s['user_id']; ?>">
<?php echo htmlspecialchars($s['firstname']." ".$s['lastname']); ?>
</option>

<?php endforeach; ?>

</select>

<button type="submit" name="assign">
<?php echo $row['assigned_staff_id'] ? 'Update' : 'Assign'; ?>
</button>

</form>
<?php endif; ?>



</td>

</tr>

<?php endforeach; ?>

</table>
</div>
<?php render_pagination($pagination, 'complaints'); ?>



<?php include('../includes/footer.php'); ?>

