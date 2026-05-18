<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'complainant'){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/complaint_updates.php');

$user_id = intval($_SESSION['user_id']);
$id = intval($_GET['id'] ?? 0);
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = intval($_GET['per_page'] ?? 10);
$perPage = in_array($perPage, [10, 20, 30, 40, 50], true) ? $perPage : 10;
$statusFilter = $_GET['status'] ?? '';
$statusFilter = in_array($statusFilter, ['Pending', 'In Progress', 'Awaiting Confirmation', 'Resolved', 'Cancelled'], true) ? $statusFilter : '';
$redirectParams = [
    'page' => $page,
    'per_page' => $perPage,
    'cancelled' => 1,
];

if($statusFilter !== ''){
    $redirectParams['status'] = $statusFilter;
}

$stmt = db_prepared_query(
    $conn,
    "UPDATE complaints
     SET status='Cancelled',
         resolution_confirmation=NULL
     WHERE complaint_id=?
     AND complainant_id=?
     AND status='Pending'",
    'ii',
    [$id, $user_id]
);

$cancelled = $stmt ? mysqli_stmt_affected_rows($stmt) : 0;
if($stmt){
    mysqli_stmt_close($stmt);
}

if($cancelled > 0){
    addComplaintUpdate(
        $conn,
        $id,
        $user_id,
        'complainant',
        'cancelled',
        'Cancelled',
        'Complaint cancelled by complainant before staff action.'
    );

    db_execute(
        $conn,
        "INSERT INTO logs (user_id, action)
         VALUES (?, ?)",
        'is',
        [$user_id, "Cancelled complaint ID $id"]
    );
}

header("Location: my_complaints.php?" . http_build_query($redirectParams));
exit();
?>
