<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!headers_sent()){
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Barangay Digital Complaint Desk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <?php $style_version = filemtime(__DIR__ . '/../css/style.css'); ?>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo $style_version; ?>">
    <script>
        const currentSessionRole = <?php echo json_encode($_SESSION['role'] ?? null); ?>;

        function redirectBySessionState(data) {
            if (!data.authenticated) {
                window.location.href = '../auth/login.php';
                return;
            }

            if (currentSessionRole && data.role !== currentSessionRole) {
                window.location.href = '../index.php';
            }
        }

        function checkSessionState() {
            fetch('../auth/session_status.php', {
                cache: 'no-store',
                credentials: 'same-origin'
            })
                .then(function(response) {
                    return response.ok ? response.json() : null;
                })
                .then(function(data) {
                    if (data) {
                        redirectBySessionState(data);
                    }
                })
                .catch(function() {});
        }

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
                return;
            }

            checkSessionState();
        });

        window.addEventListener('focus', checkSessionState);

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                checkSessionState();
            }
        });
    </script>
</head>
<body>
