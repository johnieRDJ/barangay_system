<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
require_once __DIR__ . '/../includes/notifications.php';
include('../includes/pagination.php');

$message = '';
$error = '';
$recipientRole = $_POST['recipient_role'] ?? 'all';
$allowedRecipients = ['all', 'complainant', 'staff', 'admin'];

if(isset($_POST['create_announcement'])){
    $recipientRole = in_array($recipientRole, $allowedRecipients, true) ? $recipientRole : 'all';
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['message'] ?? '');
    $announcementDate = trim($_POST['announcement_date'] ?? '');
    $announcementDate = $announcementDate !== '' ? $announcementDate : date('Y-m-d');

    if($subject === '' || $body === ''){
        $error = 'Please add both a subject and announcement message.';
    } else {
        db_execute($conn,
        "INSERT INTO announcements (created_by, recipient_role, subject, message, announcement_date)
         VALUES (?, ?, ?, ?, ?)",
         'issss',
         [intval($_SESSION['user_id']), $recipientRole, $subject, $body, $announcementDate]);

        if($recipientRole === 'all'){
            $recipients = db_select_all($conn,
            "SELECT user_id FROM users WHERE account_status='approved'");

            foreach($recipients as $recipient){
                notify_user($conn, intval($recipient['user_id']), $subject, $body, null);
            }
        } else {
            notify_role($conn, $recipientRole, $subject, $body, null);
        }

        db_execute($conn,
        "INSERT INTO logs (user_id, action) VALUES (?, ?)",
        'is',
        [intval($_SESSION['user_id']), 'Created announcement: ' . $subject]);

        $message = 'Announcement sent successfully.';
        $recipientRole = 'all';
    }
}

$pagination = pagination_state($conn,
"SELECT COUNT(*) AS total FROM announcements");

$announcements = db_select_all($conn,
"SELECT announcements.*, users.firstname, users.lastname
 FROM announcements
 LEFT JOIN users ON announcements.created_by = users.user_id
 ORDER BY announcements.created_at DESC, announcements.announcement_id DESC" . $pagination['limit_sql']);

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<div class="page-shell">
    <div class="page-header-row">
        <div class="page-title-block">
            <h1>Announcements</h1>
            <p>Create a system notification for selected users.</p>
        </div>
    </div>

    <?php if($message !== ''): ?>
        <div class="table-card"><p style="margin:0; color:#15803d; font-weight:600;"><?php echo htmlspecialchars($message); ?></p></div>
    <?php endif; ?>

    <?php if($error !== ''): ?>
        <div class="table-card"><p style="margin:0; color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($error); ?></p></div>
    <?php endif; ?>

    <form method="POST" class="filters-bar announcement-form">
        <div class="filter-group">
            <label for="recipient_role">Recipients</label>
            <select name="recipient_role" id="recipient_role">
                <option value="all" <?php echo $recipientRole === 'all' ? 'selected' : ''; ?>>All Approved Users</option>
                <option value="complainant" <?php echo $recipientRole === 'complainant' ? 'selected' : ''; ?>>Complainants</option>
                <option value="staff" <?php echo $recipientRole === 'staff' ? 'selected' : ''; ?>>Staff</option>
                <option value="admin" <?php echo $recipientRole === 'admin' ? 'selected' : ''; ?>>Admins</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="announcement_date">Date</label>
            <input type="date" name="announcement_date" id="announcement_date" value="<?php echo htmlspecialchars($_POST['announcement_date'] ?? date('Y-m-d')); ?>">
        </div>

        <div class="filter-group filter-search">
            <label for="subject">Subject</label>
            <input type="text" name="subject" id="subject" maxlength="180" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
        </div>

        <div class="filter-group" style="flex-basis:100%;">
            <label for="message">Description</label>
            <textarea name="message" id="message" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
        </div>

        <div class="filter-group filter-actions">
            <div class="filter-primary-action">
                <button type="submit" name="create_announcement">Send Announcement</button>
            </div>
        </div>
    </form>

    <div class="table-card">
        <table border="1" cellpadding="10" width="100%" class="responsive-table">
            <tr>
                <th>Date</th>
                <th>Recipients</th>
                <th>Subject</th>
                <th>Description</th>
                <th>Created By</th>
            </tr>
            <?php foreach($announcements as $announcement): ?>
                <tr>
                    <td><?php echo htmlspecialchars(date('F j, Y', strtotime($announcement['announcement_date'] ?: $announcement['created_at']))); ?></td>
                    <td><?php echo htmlspecialchars($announcement['recipient_role'] === 'all' ? 'All Approved Users' : ucfirst($announcement['recipient_role'])); ?></td>
                    <td><?php echo htmlspecialchars($announcement['subject']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></td>
                    <td><?php echo htmlspecialchars(trim(($announcement['firstname'] ?? '') . ' ' . ($announcement['lastname'] ?? '')) ?: 'Admin'); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php render_pagination($pagination, 'announcements'); ?>
</div>

<?php include('../includes/footer.php'); ?>
