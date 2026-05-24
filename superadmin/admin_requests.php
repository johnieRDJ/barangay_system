<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'superadmin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/pagination.php');

$message = '';
$messageType = 'success';

if(isset($_POST['review_request'])){
    $requestId = intval($_POST['request_id'] ?? 0);
    $decision = $_POST['decision'] ?? '';

    $request = db_select_one($conn,
    "SELECT admin_action_requests.*,
            target.role AS target_role
     FROM admin_action_requests
     JOIN users target ON admin_action_requests.target_user_id = target.user_id
     WHERE admin_action_requests.request_id=?
     AND admin_action_requests.status='pending'
     LIMIT 1",
     'i',
     [$requestId]);

    if(!$request || !in_array($decision, ['approved', 'rejected'], true)){
        $message = 'Request could not be reviewed.';
        $messageType = 'error';
    } elseif($request['target_role'] !== 'admin'){
        db_execute($conn,
        "UPDATE admin_action_requests
         SET status='rejected', reviewed_by=?, reviewed_at=NOW()
         WHERE request_id=?",
         'ii',
         [intval($_SESSION['user_id']), $requestId]);
        $message = 'Request rejected because the target account is no longer an admin.';
        $messageType = 'error';
    } elseif($decision === 'rejected'){
        db_execute($conn,
        "UPDATE admin_action_requests
         SET status='rejected', reviewed_by=?, reviewed_at=NOW()
         WHERE request_id=?",
         'ii',
         [intval($_SESSION['user_id']), $requestId]);
        $message = 'Request rejected.';
    } else {
        if($request['action_type'] === 'delete'){
            $applied = db_execute($conn,
            "DELETE FROM users WHERE user_id=? AND role='admin'",
            'i',
            [intval($request['target_user_id'])]);
        } elseif($request['action_type'] === 'edit'){
            $payload = json_decode($request['payload'] ?? '', true);
            $applied = false;

            if(is_array($payload)){
                $requestedEmail = trim($payload['email'] ?? '');
                $existingEmail = $requestedEmail !== ''
                    ? db_select_one(
                        $conn,
                        "SELECT user_id FROM users WHERE email=? AND user_id<>? LIMIT 1",
                        'si',
                        [$requestedEmail, intval($request['target_user_id'])]
                    )
                    : null;

                if($existingEmail){
                    $message = 'Request could not be approved because the email is already registered.';
                    $messageType = 'error';
                } else {
                    if(!empty($payload['password_hash'])){
                        $applied = db_execute($conn,
                        "UPDATE users SET firstname=?, lastname=?, email=?, account_status=?, password=?
                         WHERE user_id=? AND role='admin'",
                         'sssssi',
                         [
                            $payload['firstname'] ?? '',
                            $payload['lastname'] ?? '',
                            $requestedEmail,
                            $payload['account_status'] ?? 'pending',
                            $payload['password_hash'],
                            intval($request['target_user_id'])
                         ]);
                    } else {
                        $applied = db_execute($conn,
                        "UPDATE users SET firstname=?, lastname=?, email=?, account_status=?
                         WHERE user_id=? AND role='admin'",
                         'ssssi',
                         [
                            $payload['firstname'] ?? '',
                            $payload['lastname'] ?? '',
                            $requestedEmail,
                            $payload['account_status'] ?? 'pending',
                            intval($request['target_user_id'])
                         ]);
                    }
                }
            }
        } else {
            $applied = false;
        }

        if(!isset($applied) || !$applied){
            if($message === ''){
                $message = 'Request could not be applied.';
                $messageType = 'error';
            }
        } else {
            db_execute($conn,
            "UPDATE admin_action_requests
             SET status='approved', reviewed_by=?, reviewed_at=NOW()
             WHERE request_id=?",
             'ii',
             [intval($_SESSION['user_id']), $requestId]);

            db_execute($conn,
            "INSERT INTO logs (user_id, action)
             VALUES (?, ?)",
             'is',
             [intval($_SESSION['user_id']), "Approved admin {$request['action_type']} request ID $requestId"]);

            $message = 'Request approved.';
        }
    }
}

$pagination = pagination_state($conn,
"SELECT COUNT(*) AS total
 FROM admin_action_requests");

$requests = db_select_all($conn,
"SELECT admin_action_requests.*,
        requester.firstname AS requester_firstname,
        requester.lastname AS requester_lastname,
        target.firstname AS target_firstname,
        target.lastname AS target_lastname,
        target.email AS target_email
 FROM admin_action_requests
 JOIN users requester ON admin_action_requests.requested_by = requester.user_id
 LEFT JOIN users target ON admin_action_requests.target_user_id = target.user_id
 ORDER BY admin_action_requests.status='pending' DESC,
          admin_action_requests.created_at DESC" . $pagination['limit_sql']);

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<div class="page-shell">
    <div class="page-header-row">
        <div class="page-title-block">
            <h1>Admin Requests</h1>
            <p>Review admin-to-admin edit and delete requests before they affect another admin account.</p>
        </div>
    </div>

    <?php if($message !== ''): ?>
        <div class="table-card">
            <p style="margin:0; color:<?php echo $messageType === 'success' ? '#166534' : '#b91c1c'; ?>; font-weight:700;">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <table border="1" cellpadding="10" width="100%" class="responsive-table">
            <tr>
                <th>Requested By</th>
                <th>Target Admin</th>
                <th>Action</th>
                <th>Details</th>
                <th>Status</th>
                <th>Review</th>
            </tr>
            <?php foreach($requests as $request): ?>
                <?php
                    $payload = json_decode($request['payload'] ?? '', true);
                    $detailLines = [];
                    if(is_array($payload)){
                        $detailLines[] = 'Name: ' . trim(($payload['firstname'] ?? '') . ' ' . ($payload['lastname'] ?? ''));
                        $detailLines[] = 'Email: ' . ($payload['email'] ?? '');
                        $detailLines[] = 'Status: ' . ($payload['account_status'] ?? '');
                        if(!empty($payload['password_hash'])){
                            $detailLines[] = 'Password: will be changed';
                        }
                    }
                ?>
                <tr>
                    <td><?php echo htmlspecialchars(trim($request['requester_firstname'] . ' ' . $request['requester_lastname'])); ?></td>
                    <td>
                        <?php echo htmlspecialchars(trim(($request['target_firstname'] ?? '') . ' ' . ($request['target_lastname'] ?? '')) ?: 'Deleted or missing account'); ?><br>
                        <span class="table-muted"><?php echo htmlspecialchars($request['target_email'] ?? ''); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars(ucfirst($request['action_type'])); ?></td>
                    <td>
                        <?php if(!empty($detailLines)): ?>
                            <?php echo nl2br(htmlspecialchars(implode("\n", $detailLines), ENT_QUOTES, 'UTF-8')); ?>
                        <?php else: ?>
                            <span class="table-muted">Delete admin account</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars(ucfirst($request['status'])); ?></td>
                    <td>
                        <?php if($request['status'] === 'pending'): ?>
                            <form method="POST" class="inline-action-form">
                                <input type="hidden" name="request_id" value="<?php echo intval($request['request_id']); ?>">
                                <button type="submit" name="decision" value="approved" <?php echo $request['action_type'] === 'delete' ? 'data-confirm-message="Approve this delete request? The admin account will be permanently deleted."' : ''; ?>>Approve</button>
                                <button type="submit" name="decision" value="rejected" class="action-reject">Reject</button>
                                <input type="hidden" name="review_request" value="1">
                            </form>
                        <?php else: ?>
                            <span class="table-muted">
                                Reviewed <?php echo htmlspecialchars($request['reviewed_at'] ?? ''); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php render_pagination($pagination, 'admin requests'); ?>
</div>

<?php include('../includes/footer.php'); ?>
