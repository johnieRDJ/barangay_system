<?php
session_start();

/*
|--------------------------------------------------------------------------
| INDEX ROUTER
|--------------------------------------------------------------------------
| This file checks if user is logged in
| and redirects based on role
*/

// If NOT logged in → Login Page
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// If logged in → redirect by role
switch ($_SESSION['role']) {

    case 'admin':
        header("Location: admin/dashboard.php");
        break;

    case 'staff':
        header("Location: staff/dashboard.php");
        break;

    case 'complainant':
        header("Location: complainant/dashboard.php");
        break;

    default:
        // Unknown role → logout for safety
        session_destroy();
        header("Location: auth/login.php");
        break;
}

exit();
?>