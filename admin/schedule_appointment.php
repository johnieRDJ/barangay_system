<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
require_once __DIR__ . '/../includes/send_residency_schedule.php';
require_once __DIR__ . '/../includes/notifications.php';

$user_id = intval($_GET['id'] ?? 0);
$form_error = '';

$target = db_select_one(
    $conn,
    "SELECT firstname, lastname, email, role FROM users WHERE user_id=? LIMIT 1",
    'i',
    [$user_id]
);

if(!$target){
    header("Location: manage_users.php");
    exit();
}

if($target['role'] == 'superadmin'){
    echo "<script>alert('Superadmin account is protected.'); window.location='manage_users.php';</script>";
    exit();
}

if(isset($_POST['schedule'])){
    $date = $_POST['appointment_date'] ?? '';

    if($date === ''){
        $form_error = 'Please choose an appointment date.';
    } else {
        db_execute(
            $conn,
            "INSERT INTO appointments (user_id, appointment_date, purpose)
             VALUES (?, ?, ?)",
            'iss',
            [$user_id, $date, 'Barangay Residency Verification']
        );

        $stmt = db_prepared_query(
            $conn,
            "UPDATE residency
             SET status='pending'
             WHERE user_id=?
             AND status='none'",
            'i',
            [$user_id]
        );

        $updated = $stmt ? mysqli_stmt_affected_rows($stmt) : 0;
        if($stmt){
            mysqli_stmt_close($stmt);
        }

        if($updated == 0){
            db_execute(
                $conn,
                "INSERT INTO residency (user_id, status)
                 SELECT ?, 'pending'
                 WHERE NOT EXISTS (
                     SELECT 1 FROM residency WHERE user_id=?
                 )",
                'ii',
                [$user_id, $user_id]
            );
        }

        $fullname = $target['firstname']." ".$target['lastname'];
        $formatted_date = date("F d, Y - g:i A", strtotime($date));
        $mailSent = sendResidencySchedule($target['email'], $fullname, $formatted_date);

        notify_user(
            $conn,
            $user_id,
            'Barangay Residency Appointment Schedule',
            'Your residency appointment has been scheduled for ' . $formatted_date . '. Please visit the Barangay Office at the scheduled time and bring a valid ID.',
            null
        );

        db_execute(
            $conn,
            "INSERT INTO logs (user_id, action)
             VALUES (?, ?)",
            'is',
            [intval($_SESSION['user_id']), "Scheduled residency appointment for user ID $user_id"]
        );

        $alertMessage = $mailSent
            ? 'Appointment scheduled and email sent!'
            : 'Appointment scheduled. System notification was sent, but the email could not be sent.';

        echo "<script>
        alert(" . json_encode($alertMessage) . ");
        window.location='manage_users.php';
        </script>";
        exit();
    }
}

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<h2>Schedule Residency Appointment</h2>

<?php if($form_error !== ''): ?>
    <div class="table-card">
        <p style="margin:0; color:#b91c1c; font-weight:600;"><?php echo htmlspecialchars($form_error); ?></p>
    </div>
<?php endif; ?>

<form method="POST">
    <input type="datetime-local" name="appointment_date" required>
    <button type="submit" name="schedule">Schedule</button>
</form>

<?php include('../includes/footer.php'); ?>
