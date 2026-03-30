<?php
session_start();
include('../config/database.php');
include('../includes/header.php');
include('../includes/sidebar.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

// ============================
// 🔍 SEARCH & FILTER
// ============================
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';
$residency = $_GET['residency'] ?? '';

$query = "SELECT * FROM users WHERE role != 'admin'";

// SEARCH
if($search){
    $query .= " AND (firstname LIKE '%$search%' 
                OR lastname LIKE '%$search%' 
                OR email LIKE '%$search%')";
}

// FILTERS
if($role){
    $query .= " AND role='$role'";
}

if($status){
    $query .= " AND account_status='$status'";
}

if($residency){
    $query .= " AND residency_status='$residency'";
}

$result = mysqli_query($conn, $query);
?>

<h1>User Profiles</h1>

<!-- 🔍 SEARCH + FILTER -->
<form method="GET" style="margin-bottom:20px;">

    <input type="text" name="search" placeholder="Search name/email"
    value="<?php echo $search; ?>">

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

<!-- 🧾 PROFILE LIST -->
<div style="display:flex; flex-wrap:wrap; gap:20px;">

<?php while($row = mysqli_fetch_assoc($result)): ?>

<div style="border:1px solid #ccc; padding:15px; width:300px;">

    <!-- 🖼 PROFILE IMAGE -->
    <?php if($row['profile_image']): ?>
        <img src="../uploads/profile/<?php echo $row['profile_image']; ?>" width="100%">
    <?php else: ?>
        <p>No Image</p>
    <?php endif; ?>

    <h3><?php echo $row['firstname']." ".$row['lastname']; ?></h3>

    <p><strong>Email:</strong> <?php echo $row['email']; ?></p>

    <p><strong>Role:</strong> <?php echo ucfirst($row['role']); ?></p>

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

    <p><strong>Phone:</strong> <?php echo $row['phone'] ?: 'N/A'; ?></p>

    <p><strong>Address:</strong> <?php echo $row['address'] ?: 'N/A'; ?></p>

    <p><strong>About:</strong><br>
    <?php echo $row['about'] ?: 'No description'; ?>
    </p>

</div>

<?php endwhile; ?>

</div>

<?php include('../includes/footer.php'); ?>