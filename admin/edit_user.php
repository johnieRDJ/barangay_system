<?php
session_start();
include('../config/database.php');
include('../includes/validation.php');
require_once __DIR__ . '/../includes/notifications.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);
$form_error = '';

$user = db_select_one(
    $conn,
    "SELECT * FROM users WHERE user_id=? LIMIT 1",
    'i',
    [$id]
);

if(!$user){
    header("Location: manage_users.php");
    exit();
}

if($user['role'] == 'superadmin'){
    echo "<script>alert('Superadmin account is protected.'); window.location='manage_users.php';</script>";
    exit();
}

if(isset($_POST['update'])){
    $fname = barangay_clean_name($_POST['firstname'] ?? '');
    $lname = barangay_clean_name($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['account_status'] ?? 'pending';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if($fname === '' || $lname === '' || $email === ''){
        $form_error = 'Please complete all fields.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $form_error = 'Please enter a valid email address.';
    } elseif(!in_array($status, ['pending', 'approved', 'rejected'], true)){
        $form_error = 'Invalid account status.';
    } elseif($newPassword !== '' && strlen($newPassword) < 6){
        $form_error = 'New password must be at least 6 characters.';
    } elseif($newPassword !== '' && $newPassword !== $confirmPassword){
        $form_error = 'New password and confirm password do not match.';
    } else {
        $passwordHash = $newPassword !== '' ? password_hash($newPassword, PASSWORD_DEFAULT) : null;

        if($user['role'] === 'admin'){
            $payload = [
                'firstname' => $fname,
                'lastname' => $lname,
                'email' => $email,
                'account_status' => $status,
                'password_hash' => $passwordHash,
            ];

            db_execute($conn,
            "INSERT INTO admin_action_requests (requested_by, target_user_id, action_type, payload)
             VALUES (?, ?, 'edit', ?)",
             'iis',
             [intval($_SESSION['user_id']), $id, json_encode($payload)]);

            $superadmins = db_select_all($conn,
            "SELECT user_id FROM users WHERE role='superadmin'");
            foreach($superadmins as $superadmin){
                notify_user(
                    $conn,
                    intval($superadmin['user_id']),
                    'Admin Edit Request',
                    'An admin requested changes to admin account ' . trim($user['firstname'] . ' ' . $user['lastname']) . '.',
                    '../superadmin/admin_requests.php'
                );
            }

            db_execute($conn,
            "INSERT INTO logs (user_id, action)
             VALUES (?, ?)",
             'is',
             [intval($_SESSION['user_id']), "Requested edit approval for admin ID $id"]);

            $_SESSION['status_message'] = 'Edit request sent to superadmin for approval.';
            header("Location: manage_users.php");
            exit();
        }

        if($passwordHash !== null){
            db_execute($conn,
            "UPDATE users SET
             firstname=?,
             lastname=?,
             email=?,
             account_status=?,
             password=?
             WHERE user_id=? AND role IN ('complainant','staff')",
             'sssssi',
             [$fname, $lname, $email, $status, $passwordHash, $id]);
        } else {
            db_execute($conn,
            "UPDATE users SET
             firstname=?,
             lastname=?,
             email=?,
             account_status=?
             WHERE user_id=? AND role IN ('complainant','staff')",
             'ssssi',
             [$fname, $lname, $email, $status, $id]);
        }

        if($status == 'approved'){
            db_execute($conn,
            "INSERT INTO residency (user_id, status)
             SELECT ?, 'pending'
             WHERE NOT EXISTS (
                 SELECT 1 FROM residency WHERE user_id=?
             )",
             'ii',
             [$id, $id]);
        }

        db_execute($conn,
        "INSERT INTO logs (user_id, action)
         VALUES (?, ?)",
         'is',
         [intval($_SESSION['user_id']), "Updated user ID $id"]);

        header("Location: manage_users.php");
        exit();
    }
}

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<h2>Edit User</h2>

<?php if($form_error !== ''): ?>
    <p style="color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($form_error); ?></p>
<?php endif; ?>

<?php if($user['role'] === 'admin'): ?>
    <p style="color:#92400e; font-weight:700;">Changes to another admin account require superadmin approval.</p>
<?php endif; ?>

<form method="POST">

<input type="text" name="firstname" pattern="[A-Za-z .'-]+" data-alpha-only value="<?php echo htmlspecialchars($user['firstname']); ?>"><br><br>
<input type="text" name="lastname" pattern="[A-Za-z .'-]+" data-alpha-only value="<?php echo htmlspecialchars($user['lastname']); ?>"><br><br>
<input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"><br><br>
<input type="password" name="new_password" placeholder="New password (optional)"><br><br>
<input type="password" name="confirm_password" placeholder="Confirm new password"><br><br>

<select name="account_status">
    <option value="pending" <?php if($user['account_status']=='pending') echo 'selected'; ?>>Pending</option>
    <option value="approved" <?php if($user['account_status']=='approved') echo 'selected'; ?>>Approved</option>
    <option value="rejected" <?php if($user['account_status']=='rejected') echo 'selected'; ?>>Rejected</option>
</select><br><br>

<button name="update">Update</button>

</form>

<?php include('../includes/footer.php'); ?>
