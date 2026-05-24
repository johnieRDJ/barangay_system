<?php
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/notifications.php';

if(!isset($conn) || !$conn){
    die('Database connection not available');
}

$profileUserId = intval($_SESSION['user_id'] ?? 0);
$profileRole = $_SESSION['role'] ?? '';
$allowSignature = in_array($profileRole, ['admin', 'staff'], true);
$profileMessage = '';
$profileError = '';

$user = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'address' => '',
    'purok' => '',
    'phone' => '',
    'birthdate' => '',
    'age' => null,
    'gender' => '',
    'civil_status' => '',
    'name_suffix' => '',
    'about' => '',
    'profile_image' => '',
    'signature_image' => '',
    'valid_id_image' => '',
    'residency_status' => ''
];

if($profileUserId > 0){
    $check = db_select_one($conn,
    "SELECT profile_id FROM user_profiles WHERE user_id=? LIMIT 1",
    'i',
    [$profileUserId]);

    if(!$check){
        db_execute($conn, "INSERT INTO user_profiles (user_id) VALUES (?)", 'i', [$profileUserId]);
    }

    $fetchedUser = db_select_one($conn,
    "SELECT u.firstname, u.lastname, u.email,
            p.address, p.purok, p.phone, p.birthdate, p.age, p.gender, p.civil_status, p.name_suffix, p.about, p.profile_image, p.signature_image, p.valid_id_image,
            residency.status AS residency_status
     FROM users u
     LEFT JOIN user_profiles p ON u.user_id = p.user_id
     LEFT JOIN residency ON u.user_id = residency.user_id
     WHERE u.user_id=?
     LIMIT 1",
    'i',
    [$profileUserId]);
    
    if($fetchedUser){
        $user = $fetchedUser;
    }
    
    if(isset($_POST['request_verification'])){
        $currentResidencyStatus = $user['residency_status'] ?? '';
        $fullname = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));

        if($currentResidencyStatus === 'verified'){
            $profileMessage = 'Your residency is already verified.';
        } else {
            db_execute($conn,
            "INSERT INTO residency (user_id, status)
             VALUES (?, 'pending')
             ON DUPLICATE KEY UPDATE status = IF(status='verified', status, 'pending')",
            'i',
            [$profileUserId]);

            $user['residency_status'] = 'pending';

            notify_role(
                $conn,
                'admin',
                'Residency Verification Request',
                ($fullname !== '' ? $fullname : 'A user') . ' requested residency verification. Please schedule an appointment.',
                '../admin/schedule_appointment.php?id=' . $profileUserId
            );

            notify_user(
                $conn,
                $profileUserId,
                'Residency Verification Request Sent',
                'Your residency verification request was sent to the barangay admin. Please wait for your appointment schedule.',
                '../complainant/profile.php'
            );

            db_execute($conn,
            "INSERT INTO logs (user_id, action) VALUES (?, ?)",
            'is',
            [$profileUserId, 'Requested residency verification']);

            $profileMessage = 'Verification request sent to admin.';
        }
    } elseif(isset($_POST['save'])){
    $address = barangay_clean_address($_POST['address'] ?? '');
    $purok = in_array($_POST['purok'] ?? '', barangay_allowed_puroks(), true) ? ($_POST['purok'] ?? '') : '';
    $purokValue = $purok !== '' ? intval($purok) : null;
    $phoneTail = barangay_clean_phone($_POST['phone_tail'] ?? '');
    $phone = $phoneTail !== '' ? '09' . $phoneTail : barangay_clean_phone($_POST['phone'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $age = barangay_calculate_age_from_birthdate($birthdate);
    $gender = in_array($_POST['gender'] ?? '', barangay_allowed_genders(), true) ? ($_POST['gender'] ?? '') : '';
    $civilStatus = in_array($_POST['civil_status'] ?? '', barangay_allowed_civil_statuses(), true) ? ($_POST['civil_status'] ?? '') : '';
    $nameSuffix = in_array($_POST['name_suffix'] ?? '', barangay_allowed_suffixes(), true) ? ($_POST['name_suffix'] ?? '') : '';
    $about = trim($_POST['about'] ?? '');
    $errors = barangay_validate_profile_fields($address, $phone, $age);

    if($birthdate !== '' && $age === null){
        $errors[] = 'Please enter a valid birthdate.';
    }

    if($profileRole === 'complainant' && ($birthdate === '' || $age === null || $age < 18)){
        $errors[] = 'Complainants must be 18 years old or above.';
    }

    if(empty($errors)){
        $current = db_select_one($conn,
        "SELECT profile_image, signature_image, valid_id_image FROM user_profiles WHERE user_id=? LIMIT 1",
        'i',
        [$profileUserId]) ?: [];

        $profileImage = $current['profile_image'] ?? null;
        if(!empty($_FILES['image']['name'])){
            $newImage = barangay_upload_image($_FILES['image'], '../uploads/profile', $profileUserId, ['jpg', 'jpeg', 'png'], barangay_max_image_upload_bytes());
            if($newImage){
                if(!empty($profileImage)){
                    @unlink('../uploads/profile/' . $profileImage);
                }
                $profileImage = $newImage;
            } else {
                $errors[] = 'Profile image must be a JPG/JPEG or PNG image up to ' . barangay_max_upload_label() . '.';
            }
        }

        $validIdImage = $current['valid_id_image'] ?? null;
        if(!empty($_FILES['valid_id']['name'])){
            $newValidId = barangay_upload_image($_FILES['valid_id'], '../uploads/valid_ids', $profileUserId, ['jpg', 'jpeg', 'png'], barangay_max_image_upload_bytes());
            if($newValidId){
                if(!empty($validIdImage)){
                    @unlink('../uploads/valid_ids/' . $validIdImage);
                }
                $validIdImage = $newValidId;
            } else {
                $errors[] = 'Valid ID must be a JPG/JPEG or PNG image up to ' . barangay_max_upload_label() . '.';
            }
        }

        $signatureImage = $current['signature_image'] ?? null;
        if($allowSignature && !empty($_FILES['signature']['name'])){
            $newSignature = barangay_process_signature_upload($_FILES['signature'], '../uploads/signatures', $profileUserId);
            if($newSignature){
                if(!empty($signatureImage)){
                    @unlink('../uploads/signatures/' . $signatureImage);
                }
                $signatureImage = $newSignature;
            } else {
                $errors[] = 'E-signature must be a JPG/JPEG or PNG image up to ' . barangay_max_upload_label() . '.';
            }
        }
    }

    if(empty($errors)){
        $profileUpdated = db_execute($conn,
        "UPDATE user_profiles
         SET address=?,
             purok=?,
             phone=?,
             birthdate=?,
             age=?,
             gender=?,
             civil_status=?,
             name_suffix=?,
             about=?,
             profile_image=?,
             valid_id_image=?,
             signature_image=?
         WHERE user_id=?",
        'sississsssssi',
        [$address, $purokValue, $phone, $birthdate, $age, $gender, $civilStatus, $nameSuffix, $about, $profileImage, $validIdImage, $signatureImage, $profileUserId]);

        if($profileUpdated){
            db_execute($conn,
            "INSERT INTO logs (user_id, action) VALUES (?, ?)",
            'is',
            [$profileUserId, 'Updated profile information']);

            $user['address'] = $address;
            $user['purok'] = $purokValue;
            $user['phone'] = $phone;
            $user['birthdate'] = $birthdate;
            $user['age'] = $age;
            $user['gender'] = $gender;
            $user['civil_status'] = $civilStatus;
            $user['name_suffix'] = $nameSuffix;
            $user['about'] = $about;
            $user['profile_image'] = $profileImage;
            $user['valid_id_image'] = $validIdImage;
            $user['signature_image'] = $signatureImage;

            $profileMessage = 'Profile saved successfully.';
        } else {
            $profileError = 'Profile could not be saved. Please try again.';
        }
    } else {
        $profileError = implode(' ', $errors);
    }
}
}

$phoneTailValue = '';
if(!empty($user['phone']) && preg_match('/^09(\d{9})$/', barangay_clean_phone($user['phone']), $phoneMatch)){
    $phoneTailValue = $phoneMatch[1];
}
?>

<h2>My Profile</h2>

<?php if($profileMessage !== ''): ?>
    <div class="table-card"><p style="margin:0; color:#15803d; font-weight:600;"><?php echo htmlspecialchars($profileMessage); ?></p></div>
<?php endif; ?>

<?php if($profileError !== ''): ?>
    <div class="table-card"><p style="margin:0; color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($profileError); ?></p></div>
<?php endif; ?>

<div class="profile-panel">
    <form method="POST" action="<?php echo htmlspecialchars(basename($_SERVER['SCRIPT_NAME'] ?? 'profile.php'), ENT_QUOTES, 'UTF-8'); ?>" enctype="multipart/form-data" class="profile-form">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo barangay_max_image_upload_bytes(); ?>">
        <div class="profile-media-grid">
            <div>
                <h3>Profile Image</h3>
                <?php if(!empty($user['profile_image'])): ?>
                    <img src="../includes/view_profile_media.php?type=profile&amp;v=<?php echo urlencode($user['profile_image']); ?>" width="150" alt="Profile image"><br><br>
                <?php else: ?>
                    <p>No image uploaded.</p>
                <?php endif; ?>
                <label><?php echo !empty($user['profile_image']) ? 'Replace Image' : 'Upload Image'; ?>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png">
                </label>
                <p class="table-muted">Maximum file size: <?php echo barangay_max_upload_label(); ?>.</p>
            </div>

            <div>
                <h3>Valid ID</h3>
                <?php if(!empty($user['valid_id_image'])): ?>
                    <img src="../includes/view_profile_media.php?type=valid_id&amp;v=<?php echo urlencode($user['valid_id_image']); ?>" width="150" alt="Valid ID"><br><br>
                <?php else: ?>
                    <p>No valid ID uploaded.</p>
                <?php endif; ?>
                <label><?php echo !empty($user['valid_id_image']) ? 'Replace Valid ID' : 'Upload Valid ID'; ?>
                    <input type="file" name="valid_id" accept=".jpg,.jpeg,.png">
                </label>
                <p class="table-muted">Maximum file size: <?php echo barangay_max_upload_label(); ?>.</p>
            </div>

            <?php if($allowSignature): ?>
                <div>
                    <h3>E-Signature</h3>
                    <?php if(!empty($user['signature_image'])): ?>
                        <img src="../includes/view_profile_media.php?type=signature&amp;v=<?php echo urlencode($user['signature_image']); ?>" alt="E-signature" class="signature-preview"><br><br>
                    <?php else: ?>
                        <p>No e-signature uploaded.</p>
                    <?php endif; ?>
                    <label><?php echo !empty($user['signature_image']) ? 'Replace E-Signature' : 'Upload E-Signature'; ?>
                        <input type="file" name="signature" accept=".jpg,.jpeg,.png">
                    </label>
                    <p class="table-muted">JPG/JPEG or PNG only, up to <?php echo barangay_max_upload_label(); ?>. White backgrounds are automatically cleaned when possible.</p>
                    <?php if(!extension_loaded('gd')): ?>
                        <p style="margin:8px 0 0; color:#b45309; font-weight:600;">Background cleanup needs the PHP GD extension enabled in XAMPP.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <hr>

        <p><strong>Name:</strong> <?php echo htmlspecialchars(trim($user['firstname'] . ' ' . $user['lastname'] . ' ' . ($user['name_suffix'] ?? ''))); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Residency:</strong>
            <?php if(($user['residency_status'] ?? '') === 'verified'): ?>
                <span style="color:#15803d; font-weight:700;">Verified</span>
            <?php else: ?>
                <span style="color:#b91c1c; font-weight:700;">Not yet verified</span>
                <button type="submit" name="request_verification" value="1" formnovalidate style="margin-left:8px; background:#1d4f91; color:#fff; border:none; padding:4px 12px; border-radius:4px; cursor:pointer;">Request Verification</button>
            <?php endif; ?>
        </p>

        <select name="name_suffix">
            <option value="">Suffix (optional)</option>
            <?php foreach(barangay_allowed_suffixes() as $suffix): ?>
                <?php if($suffix === '') continue; ?>
                <option value="<?php echo htmlspecialchars($suffix); ?>" <?php echo ($user['name_suffix'] ?? '') === $suffix ? 'selected' : ''; ?>><?php echo htmlspecialchars($suffix); ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="address" placeholder="Address" pattern="[A-Za-z0-9 #\-\/\.,]+" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" data-address-only>

        <select name="purok">
            <option value="">Select Purok</option>
            <?php foreach(barangay_allowed_puroks() as $purokOption): ?>
                <?php if($purokOption === '') continue; ?>
                <option value="<?php echo $purokOption; ?>" <?php echo (string)($user['purok'] ?? '') === $purokOption ? 'selected' : ''; ?>>Purok <?php echo $purokOption; ?></option>
            <?php endforeach; ?>
        </select>

        <label class="profile-field-label">Phone Number</label>
        <div class="phone-input-group">
            <span>09</span>
            <input type="text" name="phone_tail" placeholder="XXXXXXXXX" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" value="<?php echo htmlspecialchars($phoneTailValue); ?>" data-digits-only>
        </div>

        <label class="profile-field-label">Birthdate</label>
        <input type="date" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">

        <?php if(!empty($user['birthdate'])): ?>
            <p class="table-muted">Age: <?php echo intval(barangay_calculate_age_from_birthdate($user['birthdate'])); ?></p>
        <?php endif; ?>

        <select name="gender">
            <?php foreach(barangay_allowed_genders() as $gender): ?>
                <option value="<?php echo htmlspecialchars($gender); ?>" <?php echo ($user['gender'] ?? '') === $gender ? 'selected' : ''; ?>><?php echo $gender === '' ? 'Select Gender' : htmlspecialchars($gender); ?></option>
            <?php endforeach; ?>
        </select>

        <select name="civil_status">
            <?php foreach(barangay_allowed_civil_statuses() as $civil): ?>
                <option value="<?php echo htmlspecialchars($civil); ?>" <?php echo ($user['civil_status'] ?? '') === $civil ? 'selected' : ''; ?>><?php echo $civil === '' ? 'Select Civil Status' : htmlspecialchars($civil); ?></option>
            <?php endforeach; ?>
        </select>

        <textarea name="about" placeholder="About you"><?php echo htmlspecialchars($user['about'] ?? ''); ?></textarea>

        <button name="save">Save Profile</button>
    </form>
</div>
