<?php
include('../includes/header.php');

if($_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/pagination.php');
include('../includes/sidebar.php');

// Join logs with users to get fullname
$pagination = pagination_state($conn,
"SELECT COUNT(*) AS total
 FROM logs
 JOIN users ON logs.user_id = users.user_id");

$logs = db_select_all($conn,
"SELECT logs.*, CONCAT(users.firstname,' ',users.lastname) AS fullname
 FROM logs
 JOIN users ON logs.user_id = users.user_id
 ORDER BY logs.log_time DESC" . $pagination['limit_sql']);
?>

<h1>System Logs</h1>

<div class="table-card">
<table border="1" cellpadding="10" width="100%" class="responsive-table">
<tr>
    <th>User</th>
    <th>Action</th>
    <th>Date & Time</th>
</tr>

<?php foreach($logs as $row): ?>
<tr>
    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
    <td><?php echo htmlspecialchars($row['action']); ?></td>
    <td><?php echo htmlspecialchars($row['log_time']); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>
<?php render_pagination($pagination, 'logs'); ?>

<?php include('../includes/footer.php'); ?>
