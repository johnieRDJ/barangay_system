<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'superadmin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/validation.php');

$form_error = '';

if(!isset($_GET['id'])){
    header("Location: manage_admins.php");
    exit();
}

$id = intval($_GET['id']);

$user = db_select_one($conn,
"SELECT * FROM users WHERE user_id=? AND role='admin' LIMIT 1",
'i',
[$id]);

if(!$user){
    header("Location: manage_admins.php");
    exit();
}

if(isset($_POST['save'])){

    $fname = barangay_clean_name($_POST['firstname'] ?? '');
    $lname = barangay_clean_name($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    if($fname === '' || $lname === '' || $email === ''){
        $form_error = 'Please complete all required fields.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $form_error = 'Please enter a valid email address.';
    } elseif($passwordInput !== '' && strlen($passwordInput) < 6){
        $form_error = 'Password must be at least 6 characters.';
    } else {
        $existing = db_select_one(
            $conn,
            "SELECT user_id FROM users WHERE email=? AND user_id<>? LIMIT 1",
            'si',
            [$email, $id]
        );

        if($existing){
            $form_error = 'That email is already registered.';
        } else {
            if($passwordInput !== ''){
                $password = password_hash($passwordInput, PASSWORD_DEFAULT);
                $updated = db_execute($conn,
                "UPDATE users SET
                 firstname=?,
                 lastname=?,
                 email=?,
                 password=?
                 WHERE user_id=? AND role='admin'",
                 'ssssi',
                 [$fname, $lname, $email, $password, $id]);
            } else {
                $updated = db_execute($conn,
                "UPDATE users SET
                 firstname=?,
                 lastname=?,
                 email=?
                 WHERE user_id=? AND role='admin'",
                 'sssi',
                 [$fname, $lname, $email, $id]);
            }

            if($updated){
                db_execute($conn,
                "INSERT INTO logs (user_id, action)
                 VALUES (?, ?)",
                 'is',
                 [intval($_SESSION['user_id']), "Updated admin ID $id"]);

                header("Location: manage_admins.php");
                exit();
            }

            $form_error = 'Unable to update admin account. Please try again.';
        }
    }

    $user['firstname'] = $fname;
    $user['lastname'] = $lname;
    $user['email'] = $email;
}

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<h2>Edit Admin</h2>

<?php if($form_error !== ''): ?>
    <p style="color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($form_error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form method="POST">
<input type="text" name="firstname" pattern="[A-Za-z .'-]+" data-alpha-only value="<?php echo htmlspecialchars($user['firstname'], ENT_QUOTES, 'UTF-8'); ?>" required><br><br>
<input type="text" name="lastname" pattern="[A-Za-z .'-]+" data-alpha-only value="<?php echo htmlspecialchars($user['lastname'], ENT_QUOTES, 'UTF-8'); ?>" required><br><br>
<input type="email" name="email" value="<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>" required><br><br>
<input type="password" name="password" placeholder="New password (leave blank to keep current)"><br><br>

<button type="submit" name="save">Save Changes</button>
</form>

<?php include('../includes/footer.php'); ?>
