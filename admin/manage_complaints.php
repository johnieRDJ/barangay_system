<?php
session_start();

include('../config/database.php');
include('../includes/pagination.php');
include('../includes/complaint_updates.php');


include('../includes/send_complaint_update.php');

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
    $postedPerPage = intval($_POST['per_page'] ?? 10);
    $postedPerPage = in_array($postedPerPage, [10, 20, 30, 40, 50], true) ? $postedPerPage : 10;
    $redirectQuery = http_build_query([
        'page' => max(1, intval($_POST['page'] ?? 1)),
        'per_page' => $postedPerPage,
    ]);
    $redirectUrl = 'manage_complaints.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '');

    // Check if already assigned
    $check = db_select_one($conn,
    "SELECT complaints.assigned_staff_id,
            complaints.status,
            complaints.tracking_number,
            complaints.subject,
            users.email,
            users.firstname,
            users.lastname
     FROM complaints
     JOIN users ON complaints.complainant_id = users.user_id
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

    if($check['assigned_staff_id']){
        $log_msg = "Updated staff assignment for complaint ID $complaint_id";
    } else {
        $log_msg = "Assigned staff to complaint ID $complaint_id";
    }

    $staff_data = db_select_one($conn,
    "SELECT firstname, lastname
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
        db_execute($conn,
    "UPDATE complaints
     SET assigned_staff_id=?,
         status='In Progress',
         resolution_confirmation=NULL
     WHERE complaint_id=?",
     'ii',
     [$staff_id, $complaint_id]);

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
    }

    header("Location: $redirectUrl");
    exit();
}

include('../includes/header.php');
include('../includes/sidebar.php');

// ============================
//  GET DATA
// ============================
$pagination = pagination_state($conn,
"SELECT COUNT(*) AS total
 FROM complaints
 JOIN users u ON complaints.complainant_id = u.user_id
 LEFT JOIN users s ON complaints.assigned_staff_id = s.user_id");

$complaints = db_select_all($conn,
"SELECT complaints.*, 
        u.firstname AS fname, u.lastname AS lname, u.email,
        s.firstname AS staff_fname, s.lastname AS staff_lname
 FROM complaints
 JOIN users u ON complaints.complainant_id = u.user_id
 LEFT JOIN users s ON complaints.assigned_staff_id = s.user_id
 ORDER BY complaints.complaint_id DESC" . $pagination['limit_sql']);

// Only approved staff
$staffRows = db_select_all($conn,
"SELECT * FROM users 
 WHERE role='staff' AND account_status='approved'");
?>

<h2>Manage Complaints</h2>

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

<tr>

<td><span class="tracking-number compact"><?php echo htmlspecialchars($row['tracking_number']); ?></span></td>

<td><?php echo htmlspecialchars($row['fname']." ".$row['lname']); ?></td>

<td><?php echo htmlspecialchars($row['subject']); ?></td>

<td><?php echo htmlspecialchars($row['description']); ?></td>

<td>
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

<td>
<?php
if($row['staff_fname']){
    echo htmlspecialchars($row['staff_fname']." ".$row['staff_lname']);
}else{
    echo "<i>Not Assigned</i>";
}
?>
</td>

<td>

<div class="action-links" style="margin-bottom:10px;">
    <a href="../reports/print_complaint_record.php?id=<?php echo intval($row['complaint_id']); ?>">Print Record</a>
</div>

<?php if($row['status'] === 'Cancelled'): ?>
    <span class="table-muted">Cancelled by complainant</span>
<?php else: ?>
<form method="POST">

<input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">
<input type="hidden" name="page" value="<?php echo intval($pagination['page']); ?>">
<input type="hidden" name="per_page" value="<?php echo intval($pagination['per_page']); ?>">
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

