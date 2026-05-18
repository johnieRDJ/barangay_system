<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/pagination.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$search = trim($_GET['search'] ?? '');
$role = in_array($_GET['role'] ?? '', ['staff', 'complainant'], true) ? $_GET['role'] : '';
$status = in_array($_GET['status'] ?? '', ['approved', 'pending', 'rejected'], true) ? $_GET['status'] : '';
$residency = in_array($_GET['residency'] ?? '', ['verified', 'pending', 'none'], true) ? $_GET['residency'] : '';

$where = ["users.role != 'admin'"];
$types = '';
$params = [];

if($search !== ''){
    $where[] = "(users.firstname LIKE ? OR users.lastname LIKE ? OR users.email LIKE ?)";
    $searchLike = '%' . $search . '%';
    $types .= 'sss';
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
}

if($role !== ''){
    $where[] = "users.role=?";
    $types .= 's';
    $params[] = $role;
}

if($status !== ''){
    $where[] = "users.account_status=?";
    $types .= 's';
    $params[] = $status;
}

if($residency === 'none'){
    $where[] = "(residency.status='none' OR residency.status IS NULL)";
} elseif($residency !== ''){
    $where[] = "residency.status=?";
    $types .= 's';
    $params[] = $residency;
}

$whereSql = implode(' AND ', $where);

$pagination = pagination_state(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     LEFT JOIN user_profiles ON users.user_id = user_profiles.user_id
     LEFT JOIN residency ON users.user_id = residency.user_id
     WHERE $whereSql",
    $types,
    $params
);

$profiles = db_select_all(
    $conn,
    "SELECT users.*,
            user_profiles.address,
            user_profiles.phone,
            user_profiles.age,
            user_profiles.gender,
            user_profiles.civil_status,
            user_profiles.about,
            user_profiles.profile_image,
            residency.status AS residency_status
     FROM users
     LEFT JOIN user_profiles ON users.user_id = user_profiles.user_id
     LEFT JOIN residency ON users.user_id = residency.user_id
     WHERE $whereSql
     ORDER BY users.lastname, users.firstname" . $pagination['limit_sql'],
    $types,
    $params
);
?>

<h1>User Profiles</h1>

<form method="GET" class="filters-bar">
    <input type="hidden" name="page" value="1">
    <input type="hidden" name="per_page" value="<?php echo intval($pagination['per_page']); ?>">

    <input type="text" name="search" placeholder="Search name/email"
    value="<?php echo htmlspecialchars($search); ?>">

    <select name="role">
        <option value="">All Roles</option>
        <option value="staff" <?php if($role=='staff') echo 'selected'; ?>>Staff</option>
        <option value="complainant" <?php if($role=='complainant') echo 'selected'; ?>>Complainant</option>
    </select>

    <select name="status">
        <option value="">All Status</option>
        <option value="approved" <?php if($status=='approved') echo 'selected'; ?>>Approved</option>
        <option value="pending" <?php if($status=='pending') echo 'selected'; ?>>Pending</option>
        <option value="rejected" <?php if($status=='rejected') echo 'selected'; ?>>Rejected</option>
    </select>

    <select name="residency">
        <option value="">All Residency</option>
        <option value="verified" <?php if($residency=='verified') echo 'selected'; ?>>Verified</option>
        <option value="pending" <?php if($residency=='pending') echo 'selected'; ?>>Pending</option>
        <option value="none" <?php if($residency=='none') echo 'selected'; ?>>None</option>
    </select>

    <button type="submit">Filter</button>

</form>

<div class="profile-grid">

<?php foreach($profiles as $row): ?>

<div class="profile-panel profile-summary-card">

    <?php if($row['profile_image']): ?>
        <img src="../uploads/profile/<?php echo htmlspecialchars($row['profile_image']); ?>" width="100%">
    <?php else: ?>
        <p>No Image</p>
    <?php endif; ?>

    <h3><?php echo htmlspecialchars($row['firstname']." ".$row['lastname']); ?></h3>

    <p><strong>Email:</strong> <span class="profile-email"><?php echo htmlspecialchars($row['email']); ?></span></p>

    <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($row['role'])); ?></p>

    <p><strong>Status:</strong>
        <?php
        if($row['account_status'] == 'approved'){
            echo "<span style='color:green;'>Approved</span>";
        } elseif($row['account_status'] == 'pending'){
            echo "<span style='color:orange;'>Pending</span>";
        } else {
            echo "<span style='color:red;'>Rejected</span>";
        }
        ?>
    </p>

    <p><strong>Residency:</strong>
        <?php
        if($row['residency_status'] == 'verified'){
            echo "<span style='color:green;'>Verified</span>";
        } elseif($row['residency_status'] == 'pending'){
            echo "<span style='color:orange;'>Pending</span>";
        } else {
            echo "<span style='color:red;'>None</span>";
        }
        ?>
    </p>

    <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone'] ?: 'N/A'); ?></p>
    <p><strong>Age:</strong> <?php echo htmlspecialchars($row['age'] ?: 'N/A'); ?></p>
    <p><strong>Gender:</strong> <?php echo htmlspecialchars($row['gender'] ?: 'N/A'); ?></p>
    <p><strong>Civil Status:</strong> <?php echo htmlspecialchars($row['civil_status'] ?: 'N/A'); ?></p>

    <p><strong>Address:</strong> <?php echo htmlspecialchars($row['address'] ?: 'N/A'); ?></p>

    <p><strong>About:</strong><br>
    <?php echo nl2br(htmlspecialchars($row['about'] ?: 'No description')); ?>
    </p>

</div>

<?php endforeach; ?>

</div>
<?php render_pagination($pagination, 'profiles'); ?>

<?php include('../includes/footer.php'); ?>
