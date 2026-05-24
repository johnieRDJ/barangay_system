<?php
session_start();
include('../config/database.php');
include('../includes/validation.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

$form_error = '';

if(isset($_POST['add'])){
    $fname = barangay_clean_name($_POST['firstname'] ?? '');
    $lname = barangay_clean_name($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    if($fname === '' || $lname === '' || $email === '' || $passwordInput === ''){
        $form_error = 'Please complete all fields.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $form_error = 'Please enter a valid email address.';
    } else {
        $existing = db_select_one($conn, "SELECT user_id FROM users WHERE email=? LIMIT 1", 's', [$email]);

        if($existing){
            $form_error = 'Email already exists.';
        } else {
            $password = password_hash($passwordInput, PASSWORD_DEFAULT);

            db_execute($conn,
            "INSERT INTO users (firstname, lastname, email, password, role, account_status)
             VALUES (?, ?, ?, ?, 'staff', 'approved')",
             'ssss',
             [$fname, $lname, $email, $password]);

            $user_id = mysqli_insert_id($conn);

            if($user_id > 0){
                db_execute($conn,
                "INSERT INTO user_auth (user_id, email_verified)
                 VALUES (?, 1)",
                 'i',
                 [$user_id]);

                db_execute($conn,
                "INSERT INTO user_profiles (user_id)
                 VALUES (?)",
                 'i',
                 [$user_id]);

                db_execute($conn,
                "INSERT INTO residency (user_id, status)
                 VALUES (?, 'verified')",
                 'i',
                 [$user_id]);

                db_execute($conn,
                "INSERT INTO logs (user_id, action)
                 VALUES (?, ?)",
                 'is',
                 [intval($_SESSION['user_id']), 'Created new staff account']);

                header("Location: manage_users.php");
                exit();
            }

            $form_error = 'Unable to create staff account.';
        }
    }
}

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<h2>Add Staff</h2>

<?php if($form_error !== ''): ?>
    <p style="color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($form_error); ?></p>
<?php endif; ?>

<form method="POST">
<input type="text" name="firstname" placeholder="First Name" pattern="[A-Za-z .'-]+" data-alpha-only required><br><br>
<input type="text" name="lastname" placeholder="Last Name" pattern="[A-Za-z .'-]+" data-alpha-only required><br><br>
<input type="email" name="email" placeholder="Email" required><br><br>
<input type="password" name="password" placeholder="Password" required><br><br>

<button name="add">Create Staff</button>
</form>

<?php include('../includes/footer.php'); ?>
