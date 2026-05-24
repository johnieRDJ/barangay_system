<?php
session_start();
include('../includes/header.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/sidebar.php');


/* ===============================
    HANDLE IMAGE UPLOAD
================================ */
// if(isset($_POST['upload'])){
//     Developer profile upload is disabled.
// }


/* ===============================
    HANDLE DELETE IMAGE
================================ */
// if(isset($_POST['delete'])){
//     Developer profile delete is disabled.
// }


/* ===============================
    FETCH DEVELOPER DATA
================================ */
$devQuery = mysqli_query($conn, "
    SELECT
        COALESCE(NULLIF(dp.name, ''), CONCAT_WS(' ', u.firstname, u.lastname)) AS name,
        COALESCE(NULLIF(dp.email, ''), u.email) AS email,
        dp.address,
        dp.about,
        dp.image
    FROM developer_profile dp
    LEFT JOIN users u ON u.user_id = dp.user_id
    ORDER BY dp.id ASC
    LIMIT 1
");

$dev = $devQuery ? mysqli_fetch_assoc($devQuery) : null;

if (!$dev) {
    $dev = [
        'name' => 'Johnie Niel Derubio',
        'email' => 'johniedy2003@gmail.com',
        'address' => 'Aguada, Recto St. Ozamiz City',
        'about' => 'Continue studying in BS Information Technology at Northwestern Mindanao State College of Science and Technology',
        'image' => 'developer.png'
    ];
}

$dev['image'] = !empty($dev['image']) ? '../uploads/' . basename($dev['image']) : '../uploads/developer.png';



/* ===============================
    DASHBOARD COUNTS
================================ */
$total_users = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM users WHERE role != 'admin'"))['total'];

$pending_users = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM users WHERE account_status='pending'"))['total'];

$total_complaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM complaints"))['total'];

$pending_complaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM complaints WHERE status='pending'"))['total'];

$resolved_complaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM complaints WHERE status='Resolved'"))['total'];
?>

<div class="dashboard-wrapper page-shell">

    <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <p>Monitor users, complaints, and overall barangay system activity.</p>
    </div>

    <div class="stats-grid">

        <div class="stat-card">
            <h3><?php echo $total_users; ?></h3>
            <p>Total Users</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $pending_users; ?></h3>
            <p>Pending Users</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $total_complaints; ?></h3>
            <p>Total Complaints</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $pending_complaints; ?></h3>
            <p>Pending Complaints</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $resolved_complaints; ?></h3>
            <p>Resolved Complaints</p>
        </div>

    </div>

    <div class="developer-card" id="about-developer">
        <h2 style="text-align:left; margin-bottom:6px;">About Developer</h2>
        <p class="developer-note">This section cannot be modified</p>

        <div class="developer-card-inner">

            <div class="developer-card-image">
                <?php if($dev['image']): ?>
                    <img src="<?php echo $dev['image']; ?>" alt="Developer Photo">
                <?php endif; ?>
            </div>

            <div class="developer-meta">
                <span class="profile-badge">System Developer</span>

                <p><strong>Name:</strong> <?php echo $dev['name']; ?></p>
                <p><strong>Email:</strong> <?php echo $dev['email']; ?></p>
                <p><strong>Address:</strong> <?php echo $dev['address']; ?></p>
                <p><strong>About:</strong> <?php echo $dev['about']; ?></p>
            </div>

        </div>
    </div>

</div>

<?php include('../includes/footer.php'); ?>
