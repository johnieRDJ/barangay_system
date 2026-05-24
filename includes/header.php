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

        function openConfirmDialog(message, onConfirm) {
            const dialog = document.getElementById('appConfirmDialog');
            const messageEl = document.getElementById('appConfirmMessage');
            const confirmBtn = document.getElementById('appConfirmYes');
            const cancelBtn = document.getElementById('appConfirmNo');

            if (!dialog || !messageEl || !confirmBtn || !cancelBtn || typeof dialog.showModal !== 'function') {
                if (window.confirm(message)) {
                    onConfirm();
                }
                return;
            }

            messageEl.textContent = message;
            const cleanup = function () {
                confirmBtn.removeEventListener('click', confirmHandler);
                cancelBtn.removeEventListener('click', cancelHandler);
                dialog.removeEventListener('cancel', cancelHandler);
            };
            const confirmHandler = function () {
                cleanup();
                dialog.close();
                onConfirm();
            };
            const cancelHandler = function () {
                cleanup();
                dialog.close();
            };

            confirmBtn.addEventListener('click', confirmHandler);
            cancelBtn.addEventListener('click', cancelHandler);
            dialog.addEventListener('cancel', cancelHandler);
            dialog.showModal();
        }

        document.addEventListener('click', function(event) {
            const link = event.target.closest('a[data-confirm-message]');

            if (!link || link.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();
            openConfirmDialog(link.dataset.confirmMessage || 'Are you sure?', function() {
                link.dataset.confirmed = 'true';
                link.click();
                setTimeout(function() {
                    delete link.dataset.confirmed;
                }, 0);
            });
        }, true);

        document.addEventListener('submit', function(event) {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            const submitter = event.submitter;
            if (
                submitter instanceof HTMLElement &&
                submitter.hasAttribute('data-confirm-message') &&
                submitter.dataset.confirmed !== 'true'
            ) {
                event.preventDefault();
                openConfirmDialog(submitter.dataset.confirmMessage || 'Are you sure?', function() {
                    submitter.dataset.confirmed = 'true';
                    form.requestSubmit(submitter);
                    setTimeout(function() {
                        delete submitter.dataset.confirmed;
                    }, 0);
                });
                return;
            }

            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';

            setTimeout(function() {
                form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function(button) {
                    button.disabled = true;
                });
            }, 0);
        }, true);

        document.addEventListener('input', function(event) {
            const input = event.target;

            if (!(input instanceof HTMLInputElement)) {
                return;
            }

            if (input.hasAttribute('data-digits-only')) {
                const maxLength = parseInt(input.getAttribute('maxlength') || '99', 10);
                input.value = input.value.replace(/\D/g, '').slice(0, maxLength);
            }

            if (input.hasAttribute('data-alpha-only')) {
                input.value = input.value.replace(/[^A-Za-z .'-]/g, '');
            }

            if (input.hasAttribute('data-address-only')) {
                input.value = input.value.replace(/[^A-Za-z0-9 #\-\/\.,]/g, '');
            }
        });

        document.addEventListener('change', function(event) {
            const input = event.target;

            if (!(input instanceof HTMLInputElement) || input.type !== 'file') {
                return;
            }

            const maxBytes = 50 * 1024 * 1024;
            const imageOnlyInputs = ['valid_id', 'image', 'signature', 'complainant_signature'];
            const proofInputs = ['proof_files[]', 'proof_files'];
            let allowedTypes = null;

            if (imageOnlyInputs.includes(input.name)) {
                allowedTypes = ['image/jpeg', 'image/png'];
            } else if (proofInputs.includes(input.name)) {
                allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'video/mp4', 'video/quicktime', 'video/webm'];
            }

            if (!allowedTypes) {
                return;
            }

            for (const file of input.files || []) {
                if (!allowedTypes.includes(file.type) || file.size > maxBytes) {
                    input.value = '';
                    alert('Please choose an allowed file type up to 50MB.');
                    return;
                }
            }
        });
    </script>
</head>
<body>
<dialog id="appConfirmDialog" class="confirm-dialog">
    <div class="confirm-dialog-card">
        <h2>Confirm Action</h2>
        <p id="appConfirmMessage">Are you sure?</p>
        <div class="confirm-dialog-actions">
            <button type="button" id="appConfirmNo" class="secondary-action">Cancel</button>
            <button type="button" id="appConfirmYes">Continue</button>
        </div>
    </div>
</dialog>
