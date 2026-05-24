<?php
session_start();
include('../config/database.php');
include('../includes/send_otp.php');
include('../includes/validation.php');
require_once __DIR__ . '/../includes/notifications.php';

$register_error = '';
$register_success = '';
$firstname = '';
$lastname = '';
$name_suffix = '';
$email = '';
$role = '';
$birthdate = '';
$temp_valid_id = $_SESSION['temp_valid_id'] ?? null;
$temp_valid_id_name = $_SESSION['temp_valid_id_name'] ?? '';

if(isset($_POST['register'])){
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $name_suffix = $_POST['name_suffix'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $birthdate = trim($_POST['birthdate'] ?? '');
    $validIdData = trim($_POST['valid_id_data'] ?? '');
    $validIdName = trim($_POST['valid_id_name'] ?? '');
    $age = barangay_calculate_age_from_birthdate($birthdate);

    $firstname = barangay_clean_name($firstname);
    $lastname = barangay_clean_name($lastname);
    $name_suffix = in_array($name_suffix, barangay_allowed_suffixes(), true) ? $name_suffix : '';

    $has_valid_id_file = !empty($_FILES['valid_id_file']['name']) && ($_FILES['valid_id_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
    $has_valid_id_data = $validIdData !== '';
    $has_valid_id = $has_valid_id_file || $has_valid_id_data;
    $using_temp_id = !empty($temp_valid_id);

    $retainValidIdForRetry = function() use (&$temp_valid_id, &$temp_valid_id_name, $has_valid_id_data, $has_valid_id_file, $validIdData, $validIdName) {
        if($has_valid_id_data){
            $temp_valid_id = barangay_save_base64_image($validIdData, $validIdName, '../uploads/valid_ids/temp', 0, barangay_max_image_upload_bytes());
            if($temp_valid_id){
                $temp_valid_id_name = $validIdName;
                $_SESSION['temp_valid_id'] = $temp_valid_id;
                $_SESSION['temp_valid_id_name'] = $temp_valid_id_name;
            }
        } elseif($has_valid_id_file){
            $temp_valid_id = barangay_upload_image($_FILES['valid_id_file'], '../uploads/valid_ids/temp', 0, ['jpg', 'jpeg', 'png'], barangay_max_image_upload_bytes());
            if($temp_valid_id){
                $temp_valid_id_name = $_FILES['valid_id_file']['name'];
                $_SESSION['temp_valid_id'] = $temp_valid_id;
                $_SESSION['temp_valid_id_name'] = $temp_valid_id_name;
            }
        }
    };

    if($password !== $confirm_password){
        $retainValidIdForRetry();
        $register_error = 'Passwords do not match.';
    } elseif(strlen($password) < 6){
        $retainValidIdForRetry();
        $register_error = 'Password must be at least 6 characters.';
    } elseif($firstname === '' || $lastname === '' || $email === '' || $password === '' || $confirm_password === ''){
        $register_error = 'Please complete all required fields.';
        unset($_SESSION['temp_valid_id'], $_SESSION['temp_valid_id_name']);
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $register_error = 'Please enter a valid email address.';
        unset($_SESSION['temp_valid_id'], $_SESSION['temp_valid_id_name']);
    } elseif(!in_array($role, ['complainant', 'staff'], true)){
        $register_error = 'Please select a valid role.';
        unset($_SESSION['temp_valid_id'], $_SESSION['temp_valid_id_name']);
    } elseif($birthdate === '' || $age === null){
        $register_error = 'Please enter a valid birthdate.';
        unset($_SESSION['temp_valid_id'], $_SESSION['temp_valid_id_name']);
    } elseif($role === 'complainant' && $age < 18){
        $register_error = 'Complainants must be 18 years old or above.';
        unset($_SESSION['temp_valid_id'], $_SESSION['temp_valid_id_name']);
    } elseif(!$has_valid_id && !$using_temp_id){
        $register_error = 'Please attach a valid ID for admin review.';
        unset($_SESSION['temp_valid_id'], $_SESSION['temp_valid_id_name']);
    } else {
        unset($_SESSION['temp_valid_id'], $_SESSION['temp_valid_id_name']);
        $existingUser = db_select_one(
            $conn,
            "SELECT user_id FROM users WHERE email=? LIMIT 1",
            's',
            [$email]
        );

        if($existingUser){
            $register_error = 'That email is already registered. Please log in or use another email.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(16));

            db_execute(
                $conn,
                "INSERT INTO users (firstname, lastname, email, password, role)
                 VALUES (?, ?, ?, ?, ?)",
                'sssss',
                [$firstname, $lastname, $email, $password_hash, $role]
            );

            $user_id = mysqli_insert_id($conn);

            if($user_id > 0){
                $validIdImage = null;
                if($has_valid_id_data){
                    $validIdImage = barangay_save_base64_image($validIdData, $validIdName, '../uploads/valid_ids', $user_id, barangay_max_image_upload_bytes());
                    if(!$validIdImage){
                        $register_error = 'Valid ID must be a JPG/JPEG or PNG image up to ' . barangay_max_upload_label() . '.';
                    }
                } elseif($has_valid_id_file){
                    $validIdImage = barangay_upload_image($_FILES['valid_id_file'], '../uploads/valid_ids', $user_id, ['jpg', 'jpeg', 'png'], barangay_max_image_upload_bytes());
                    if(!$validIdImage){
                        $register_error = 'Valid ID must be a JPG/JPEG or PNG image up to ' . barangay_max_upload_label() . '.';
                    }
                } elseif($using_temp_id && $temp_valid_id){
                    $sourcePath = '../uploads/valid_ids/temp/' . $temp_valid_id;
                    if(file_exists($sourcePath)){
                        $storedName = time() . '_' . $user_id . '_' . basename($temp_valid_id_name ?: $temp_valid_id);
                        $destPath = '../uploads/valid_ids/' . $storedName;
                        if(rename($sourcePath, $destPath)){
                            $validIdImage = $storedName;
                        }
                    }
                }

                if($register_error !== ''){
                    db_execute($conn, "DELETE FROM users WHERE user_id=?", 'i', [$user_id]);
                } else {
                db_execute(
                    $conn,
                    "INSERT INTO user_auth (user_id, email_verified, verification_token)
                     VALUES (?, 0, ?)",
                    'is',
                    [$user_id, $token]
                );

                db_execute(
                    $conn,
                    "INSERT INTO user_profiles (user_id, birthdate, age, name_suffix, valid_id_image)
                     VALUES (?, ?, ?, ?, ?)",
                    'isiss',
                    [$user_id, $birthdate, $age, $name_suffix, $validIdImage]
                );

                db_execute(
                    $conn,
                    "INSERT INTO residency (user_id, status)
                     VALUES (?, ?)",
                    'is',
                    [$user_id, 'pending']
                );

                notify_user(
                    $conn,
                    $user_id,
                    'Welcome to the Barangay Complaint System',
                    'Thank you for registering. Please verify your email, wait for admin approval, and complete your My Profile information after your account is approved.',
                    null
                );

                $link = rtrim(APP_URL, '/') . "/auth/verify_email.php?token=" . urlencode($token);
                $fullname = trim($firstname . ' ' . $lastname);
                sendRegistrationVerificationEmail($email, $fullname, $role, $link);

                $register_success = 'Registration successful. Check your email for the verification message and next steps.';
                }
            } else {
                $register_error = 'Unable to create account. Please try again.';
            }
        }
    }
}
$temp_valid_id = $_SESSION['temp_valid_id'] ?? null;
$temp_valid_id_name = $_SESSION['temp_valid_id_name'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <?php $scriptVersion = filemtime(__DIR__ . '/../js/script.js'); ?>
    <script src="../js/script.js?v=<?php echo $scriptVersion; ?>"></script>
</head>
<body>

<div class="container">
    <h2>Register</h2>

    <?php if($register_error !== ''): ?>
        <p style="color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($register_error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if($register_success !== ''): ?>
        <p style="color:#15803d; font-weight:600;"><?php echo htmlspecialchars($register_success, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php" onsubmit="return validateRegister(event)">
        <input type="hidden" name="register" value="1">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo barangay_max_image_upload_bytes(); ?>">
        <input type="hidden" name="valid_id_data" value="">
        <input type="hidden" name="valid_id_name" value="">
        <input type="text" name="firstname" placeholder="First Name" pattern="[A-Za-z .'-]+" data-alpha-only value="<?php echo htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8'); ?>" required>
        <input type="text" name="lastname" placeholder="Last Name" pattern="[A-Za-z .'-]+" data-alpha-only value="<?php echo htmlspecialchars($lastname, ENT_QUOTES, 'UTF-8'); ?>" required>
        <select name="name_suffix">
            <option value="">Suffix (optional)</option>
            <?php foreach(barangay_allowed_suffixes() as $suffix): ?>
                <?php if($suffix === '') continue; ?>
                <option value="<?php echo htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $name_suffix === $suffix ? 'selected' : ''; ?>><?php echo htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="email" name="email" placeholder="Email Address" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
        <label class="profile-field-label">Birthdate</label>
        <input type="date" name="birthdate" value="<?php echo htmlspecialchars($birthdate, ENT_QUOTES, 'UTF-8'); ?>" required>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>

        <select name="role" required>
            <option value="">Select Role</option>
            <option value="complainant" <?php echo $role === 'complainant' ? 'selected' : ''; ?>>Complainant</option>
            <option value="staff" <?php echo $role === 'staff' ? 'selected' : ''; ?>>Staff (Recipient)</option>
        </select>

        <label class="file-field">Valid ID (JPG/PNG, required for admin review)
            <input type="file" name="valid_id_file" accept=".jpg,.jpeg,.png" <?php echo $temp_valid_id ? '' : 'required'; ?>>
        </label>
        <p class="table-muted">Maximum file size: <?php echo barangay_max_upload_label(); ?>.</p>

        <?php if($temp_valid_id): ?>
            <p style="color:#15803d; font-size:14px; margin:4px 0;">
                Valid ID retained: <?php echo htmlspecialchars($temp_valid_id_name); ?>. You do not need to attach it again.
                <a href="clear_temp_id.php" style="color:#b91c1c; margin-left:8px;">Clear</a>
            </p>
        <?php endif; ?>

        <button type="submit">Register</button>
    </form>

    <div class="link">
        <a href="login.php">Already have an account? Login</a>
    </div>
</div>

</body>
</html>
