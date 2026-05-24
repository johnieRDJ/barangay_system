<?php

if(!function_exists('notify_user')){
function notify_user(mysqli $conn, int $userId, string $subject, string $message, ?string $link = null): void
{
    if($userId <= 0){
        return;
    }

    $duplicate = db_select_one($conn,
    "SELECT notification_id
     FROM notifications
     WHERE user_id=?
     AND subject=?
     AND message=?
     AND (link <=> ?)
     AND created_at >= (NOW() - INTERVAL 30 SECOND)
     LIMIT 1",
     'isss',
     [$userId, $subject, $message, $link]);

    if($duplicate){
        return;
    }

    db_execute($conn,
    "INSERT INTO notifications (user_id, subject, message, link)
     VALUES (?, ?, ?, ?)",
     'isss',
     [$userId, $subject, $message, $link]);
}
}

if(!function_exists('notify_role')){
function notify_role(mysqli $conn, string $role, string $subject, string $message, ?string $link = null): void
{
    $users = db_select_all($conn,
    "SELECT user_id FROM users WHERE role=? AND account_status='approved'",
    's',
    [$role]);

    foreach($users as $user){
        notify_user($conn, intval($user['user_id']), $subject, $message, $link);
    }
}
}

if(!function_exists('notification_unread_count')){
function notification_unread_count(mysqli $conn, int $userId): int
{
    $row = db_select_one($conn,
    "SELECT COUNT(*) AS total
     FROM notifications
     WHERE user_id=?
     AND read_at IS NULL",
     'i',
     [$userId]);

    return $row ? intval($row['total']) : 0;
}
}
?>
