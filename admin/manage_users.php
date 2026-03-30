<?php
session_start();

include('../config/database.php');
include('../includes/header.php');
include('../includes/sidebar.php');

if($_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

// ============================
// 🔴 DELETE USER
// ============================
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);

    mysqli_query($conn,"DELETE FROM users WHERE user_id='$id'");

    mysqli_query($conn,
    "INSERT INTO logs (user_id, action)
     VALUES ('".$_SESSION['user_id']."','Deleted user ID $id')");

    header("Location: manage_users.php");
    exit();
}

// ============================
// 🔍 SEARCH FUNCTION
// ============================
$search = "";

if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $result = mysqli_query($conn, 
    "SELECT * FROM users 
     WHERE role != 'admin'
     AND (firstname LIKE '%$search%' 
     OR lastname LIKE '%$search%' 
     OR email LIKE '%$search%')");
} else {
    $result = mysqli_query($conn, 
    "SELECT * FROM users WHERE role != 'admin'");
}
?>

<h1>Manage Users</h1>

<!-- 🔍 SEARCH BAR -->
<form method="GET" style="margin-bottom:15px;">
    <input type="text" name="search" placeholder="Search name or email..." value="<?php echo $search; ?>">
    <button type="submit">Search</button>
</form>

<a href="add_user.php">➕ Add Staff</a><br><br>

<table border="1" cellpadding="10" width="100%">
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Residency</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>
<tr>

<td><?php echo $row['firstname']." ".$row['lastname']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['role']; ?></td>

<!-- ✅ FIXED RESIDENCY (NO DUPLICATION) -->
<td>
<?php 
if($row['residency_status'] == 'verified'){
    echo "<span style='color:green;'>Verified</span>";
}
elseif($row['residency_status'] == 'pending'){
    echo "<span style='color:orange;'>Pending</span>";
}
else{
    echo "<span style='color:red;'>Not Verified</span>";
}
?>
</td>

<td><?php echo $row['account_status']; ?></td>

<td>

<!-- ✅ EDIT -->
<a href="edit_user.php?id=<?php echo $row['user_id']; ?>">Edit</a> |

<!-- ✅ DELETE -->
<a href="manage_users.php?delete=<?php echo $row['user_id']; ?>"
onclick="return confirm('Delete this user?')">Delete</a> |

<!-- ✅ ACTIONS -->
<?php if($row['account_status'] == 'pending'): ?>

<a href="approve_user.php?id=<?php echo $row['user_id']; ?>">Approve</a> |

<a href="schedule_appointment.php?id=<?php echo $row['user_id']; ?>">Schedule</a> |

<a href="reject_user.php?id=<?php echo $row['user_id']; ?>">Reject</a>

<?php else: ?>
<span style="color:gray;">No Action</span>
<?php endif; ?>

</td>

</tr>
<?php endwhile; ?>

</table>

<?php include('../includes/footer.php'); ?>

