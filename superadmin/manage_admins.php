<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'superadmin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/pagination.php');

$message = $_SESSION['superadmin_message'] ?? '';
$messageType = $_SESSION['superadmin_message_type'] ?? 'success';
unset($_SESSION['superadmin_message'], $_SESSION['superadmin_message_type']);

// DELETE ADMIN
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);

    $deleted = db_execute($conn,
    "DELETE FROM users WHERE user_id=? AND role='admin'",
    'i',
    [$id]);

    if($deleted && mysqli_affected_rows($conn) > 0){
        db_execute($conn,
        "INSERT INTO logs (user_id, action)
         VALUES (?, ?)",
         'is',
         [intval($_SESSION['user_id']), "Deleted admin ID $id"]);

        $_SESSION['superadmin_message'] = 'Admin account deleted.';
        $_SESSION['superadmin_message_type'] = 'success';
    } else {
        $_SESSION['superadmin_message'] = 'Unable to delete admin account.';
        $_SESSION['superadmin_message_type'] = 'error';
    }

    header("Location: manage_admins.php");
    exit();
}

$pagination = pagination_state($conn,
"SELECT COUNT(*) AS total FROM users WHERE role='admin'");

$admins = db_select_all($conn,
"SELECT * FROM users WHERE role='admin' ORDER BY lastname, firstname" . $pagination['limit_sql']);

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<h2>Manage Admins</h2>

<?php if($message !== ''): ?>
    <div class="table-card">
        <p style="margin:0; color:<?php echo $messageType === 'success' ? '#166534' : '#b91c1c'; ?>; font-weight:700;">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    </div>
<?php endif; ?>

<a href="add_admin.php" class="page-action">Add Admin</a>

<div class="table-card">
<table border="1" cellpadding="10" class="responsive-table">
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php foreach($admins as $row): ?>
<tr>

<td><?php echo htmlspecialchars($row['firstname']." ".$row['lastname']); ?></td>
<td><?php echo htmlspecialchars($row['email']); ?></td>
<td><?php echo htmlspecialchars($row['account_status']); ?></td>

<td>
<a href="edit_admin.php?id=<?php echo $row['user_id']; ?>">Edit</a> |
<a href="?delete=<?php echo $row['user_id']; ?>" data-confirm-message="Delete this admin account? This action cannot be undone.">Delete</a>
</td>

</tr>
<?php endforeach; ?>
</table>
</div>
<?php render_pagination($pagination, 'admins'); ?>

<?php include('../includes/footer.php'); ?>
