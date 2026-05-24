<?php
session_start();

include('../config/database.php');
include('../includes/complaint_updates.php');
require_once __DIR__ . '/../includes/notifications.php';
include('../includes/validation.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'complainant'){
    header("Location: ../auth/login.php");
    exit();
}

$form_error = '';

if(empty($_SESSION['complaint_submit_token'])){
    $_SESSION['complaint_submit_token'] = bin2hex(random_bytes(16));
}

if(isset($_POST['submit'])){

    // Sanitize input to prevent SQL errors
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $submittedToken = $_POST['submit_token'] ?? '';

    $user_id = intval($_SESSION['user_id']);
    $profile = db_select_one($conn,
    "SELECT user_profiles.birthdate,
            user_profiles.age,
            user_profiles.valid_id_image,
            residency.status AS residency_status
     FROM user_profiles
     LEFT JOIN residency ON user_profiles.user_id = residency.user_id
     WHERE user_profiles.user_id=?
     LIMIT 1",
    'i',
    [$user_id]);

    $profileAge = !empty($profile['birthdate']) ? barangay_calculate_age_from_birthdate($profile['birthdate']) : intval($profile['age'] ?? 0);

    if(!hash_equals($_SESSION['complaint_submit_token'] ?? '', $submittedToken)){
        header("Location: my_complaints.php?submitted=1");
        exit();
    }

    if($profileAge < 18){
        $form_error = 'You must be 18 years old or above before submitting a complaint. Please update your birthdate in My Profile.';
    } elseif(empty($profile['valid_id_image'])){
        $form_error = 'Please upload a valid ID in My Profile before submitting a complaint.';
    } elseif(($profile['residency_status'] ?? '') !== 'verified'){
        $form_error = 'Your residency is not yet verified. Please wait for barangay verification before submitting a complaint.';
    } elseif($subject === '' || $description === ''){
        $form_error = 'Please complete the subject and complaint details.';
    } else {
        unset($_SESSION['complaint_submit_token']);

        $recentDuplicate = db_select_one($conn,
        "SELECT complaint_id
         FROM complaints
         WHERE complainant_id=?
         AND subject=?
         AND description=?
         AND created_at >= (NOW() - INTERVAL 60 SECOND)
         ORDER BY complaint_id DESC
         LIMIT 1",
         'iss',
         [$user_id, $subject, $description]);

        if($recentDuplicate){
            header("Location: my_complaints.php?submitted=1");
            exit();
        }

        // Insert complaint
        db_execute($conn,
        "INSERT INTO complaints (complainant_id, subject, description)
         VALUES (?, ?, ?)",
         'iss',
         [$user_id, $subject, $description]);

        $complaint_id = mysqli_insert_id($conn);

        if($complaint_id > 0){
            $tracking_number = 'CMP-' . date('Ymd') . '-' . str_pad((string)$complaint_id, 5, '0', STR_PAD_LEFT);
            db_execute($conn,
            "UPDATE complaints
             SET tracking_number=?
             WHERE complaint_id=?",
             'si',
             [$tracking_number, $complaint_id]);

            addComplaintUpdate(
                $conn,
                $complaint_id,
                intval($user_id),
                'complainant',
                'submitted',
                'Pending',
                'Complaint submitted by complainant.'
            );

            // Insert log
            db_execute($conn,
            "INSERT INTO logs (user_id, action)
             VALUES (?, ?)",
             'is',
             [$user_id, "Created complaint $tracking_number"]);

            notify_role(
                $conn,
                'admin',
                'New Complaint Submitted',
                'A complainant submitted a new complaint with tracking number ' . $tracking_number . '.',
                '../admin/manage_complaints.php?status=Pending&search=' . urlencode($tracking_number)
            );

            header("Location: my_complaints.php?submitted=1");
            exit();
        }

        $form_error = 'Unable to submit complaint. Please try again.';
    }
}

if(empty($_SESSION['complaint_submit_token'])){
    $_SESSION['complaint_submit_token'] = bin2hex(random_bytes(16));
}

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<h2>Submit Complaint</h2>

<?php if($form_error !== ''): ?>
    <div class="table-card">
        <p style="margin:0; color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($form_error); ?></p>
    </div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="submit_token" value="<?php echo htmlspecialchars($_SESSION['complaint_submit_token']); ?>">
    <input type="text" name="subject" placeholder="Subject" required>
    <textarea name="description" placeholder="Complaint Details" required></textarea>
    <button type="submit" name="submit">Submit</button>
</form>

<?php include('../includes/footer.php'); ?>
