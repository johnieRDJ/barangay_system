<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'superadmin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/validation.php');

$form_error = '';
$firstname = '';
$lastname = '';
$email = '';

if(isset($_POST['add'])){

    $fname = barangay_clean_name($_POST['firstname'] ?? '');
    $lname = barangay_clean_name($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    $firstname = $fname;
    $lastname = $lname;

    if($fname === '' || $lname === '' || $email === '' || $passwordInput === ''){
        $form_error = 'Please complete all fields.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $form_error = 'Please enter a valid email address.';
    } elseif(strlen($passwordInput) < 6){
        $form_error = 'Password must be at least 6 characters.';
    } else {
        $existing = db_select_one($conn, "SELECT user_id FROM users WHERE email=? LIMIT 1", 's', [$email]);

        if($existing){
            $form_error = 'That email is already registered.';
        } else {
            $password = password_hash($passwordInput, PASSWORD_DEFAULT);

            db_execute($conn,
            "INSERT INTO users (firstname, lastname, email, password, role, account_status)
             VALUES (?, ?, ?, ?, 'admin', 'approved')",
             'ssss',
             [$fname, $lname, $email, $password]);

            $user_id = mysqli_insert_id($conn);

            if($user_id > 0){
                $authCreated = db_execute($conn,
                "INSERT INTO user_auth (user_id, email_verified)
                 VALUES (?, 1)",
                 'i',
                 [$user_id]);

                $profileCreated = db_execute($conn,
                "INSERT INTO user_profiles (user_id)
                 VALUES (?)",
                 'i',
                 [$user_id]);

                $residencyCreated = db_execute($conn,
                "INSERT INTO residency (user_id, status)
                 VALUES (?, 'verified')",
                 'i',
                 [$user_id]);

                if($authCreated && $profileCreated && $residencyCreated){
                    db_execute($conn,
                    "INSERT INTO logs (user_id, action)
                     VALUES (?, ?)",
                     'is',
                     [intval($_SESSION['user_id']), 'Created new admin account']);

                    header("Location: manage_admins.php");
                    exit();
                }

                db_execute($conn, "DELETE FROM users WHERE user_id=? AND role='admin'", 'i', [$user_id]);
            }

            $form_error = 'Unable to create admin account. Please try again.';
        }
    }
}

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<h2>Add Admin</h2>

<?php if($form_error !== ''): ?>
    <p style="color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($form_error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form method="POST">
<input type="text" name="firstname" placeholder="First Name" pattern="[A-Za-z .'-]+" data-alpha-only value="<?php echo htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8'); ?>" required><br><br>
<input type="text" name="lastname" placeholder="Last Name" pattern="[A-Za-z .'-]+" data-alpha-only value="<?php echo htmlspecialchars($lastname, ENT_QUOTES, 'UTF-8'); ?>" required><br><br>
<input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required><br><br>
<input type="password" name="password" placeholder="Password" required><br><br>

<button type="submit" name="add">Create Admin</button>
</form>

<?php include('../includes/footer.php'); ?>
