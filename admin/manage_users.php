<?php
include('../includes/header.php');
include('../includes/send_account_status.php');

if($_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/sidebar.php');

$result = mysqli_query($conn, 
"SELECT * FROM users WHERE role != 'admin'");
?>

<h1>Manage Users</h1>

<table border="1" cellpadding="10">
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
    <td><?php echo $row['fullname']; ?></td>
    <td><?php echo $row['email']; ?></td>
    <td><?php echo $row['role']; ?></td>
    <td><?php echo $row['residency_status']; ?></td>
    <td><?php echo $row['account_status']; ?></td>
    <td>

    <?php if($row['account_status'] == 'pending'): ?>

        <a href="approve_user.php?id=<?php echo $row['user_id']; ?>">
            Approve
        </a> |

        <a href="schedule_appointment.php?id=<?php echo $row['user_id']; ?>">
            Schedule Residency
        </a> |

        <a href="reject_user.php?id=<?php echo $row['user_id']; ?>">
            Reject
        </a>

    <?php else: ?>
        No Action
    <?php endif; ?>

    </td>
</tr>
<?php endwhile; ?>

</table>

<?php include('../includes/footer.php'); ?>