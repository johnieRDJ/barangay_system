<?php
$userId = intval($_SESSION['user_id']);

if(isset($_GET['read'])){
    $notificationId = intval($_GET['read']);
    db_execute($conn,
    "UPDATE notifications
     SET read_at=NOW()
     WHERE notification_id=?
     AND user_id=?",
     'ii',
     [$notificationId, $userId]);
}

if(isset($_POST['mark_all_read'])){
    db_execute($conn,
    "UPDATE notifications SET read_at=NOW() WHERE user_id=? AND read_at IS NULL",
    'i',
    [$userId]);
}

if(isset($_POST['delete_notification'])){
    $notificationId = intval($_POST['notification_id'] ?? 0);

    db_execute($conn,
    "DELETE FROM notifications
     WHERE notification_id=?
     AND user_id=?",
     'ii',
     [$notificationId, $userId]);
}

if(isset($_POST['clear_notifications'])){
    db_execute($conn,
    "DELETE FROM notifications
     WHERE user_id=?",
     'i',
     [$userId]);
}

$pagination = pagination_state($conn,
"SELECT COUNT(*) AS total
 FROM notifications
 WHERE user_id=?",
 'i',
 [$userId]);

$notifications = db_select_all($conn,
"SELECT *
 FROM notifications
 WHERE user_id=?
 ORDER BY created_at DESC, notification_id DESC" . $pagination['limit_sql'],
 'i',
 [$userId]);
?>

<div class="page-shell">
    <div class="page-header-row">
        <div class="page-title-block">
            <h1>Notifications</h1>
            <p>Recent system updates, complaint handoffs, and announcements.</p>
        </div>
        <form method="POST" class="inline-action-form">
            <button type="submit" name="mark_all_read" class="secondary-action">Mark All Read</button>
            <button type="submit" name="clear_notifications" class="action-reject" data-confirm-message="Clear all notifications? This cannot be undone.">Clear Notifications</button>
        </form>
    </div>

    <div class="notification-list">
        <?php if(empty($notifications)): ?>
            <div class="table-card">
                <p class="table-muted" style="margin:0;">No notifications yet.</p>
            </div>
        <?php endif; ?>

        <?php foreach($notifications as $notification): ?>
            <div class="table-card notification-card <?php echo empty($notification['read_at']) ? 'unread' : ''; ?>">
                <div>
                    <h2><?php echo htmlspecialchars($notification['subject']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                    <p class="table-muted"><?php echo date('F j, Y g:i A', strtotime($notification['created_at'])); ?></p>
                </div>
                <div class="notification-actions">
                    <?php if(!empty($notification['link'])): ?>
                        <a class="page-action" href="<?php echo htmlspecialchars($notification['link']); ?>">Open</a>
                    <?php endif; ?>
                    <?php if(empty($notification['read_at'])): ?>
                        <a class="page-action secondary-action" href="notifications.php?read=<?php echo intval($notification['notification_id']); ?>">Mark Read</a>
                    <?php endif; ?>
                    <form method="POST" class="inline-action-form">
                        <input type="hidden" name="notification_id" value="<?php echo intval($notification['notification_id']); ?>">
                        <button type="submit" name="delete_notification" class="action-reject" data-confirm-message="Delete this notification? This cannot be undone.">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php render_pagination($pagination, 'notifications'); ?>
</div>
