<?php
require_once __DIR__ . '/app.php';

$conn = mysqli_connect(
    app_config('database.host', 'localhost'),
    app_config('database.username', 'root'),
    app_config('database.password', ''),
    app_config('database.name', 'barangay_db')
);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET time_zone = '+08:00'");

if(!function_exists('db_prepared_query')){
    function db_prepared_query(mysqli $conn, string $sql, string $types = '', array $params = [])
    {
        $stmt = mysqli_prepare($conn, $sql);

        if(!$stmt){
            return false;
        }

        if($types !== '' && !empty($params)){
            $bindParams = [$types];

            foreach($params as $key => $value){
                $bindParams[] = &$params[$key];
            }

            if(!call_user_func_array([$stmt, 'bind_param'], $bindParams)){
                mysqli_stmt_close($stmt);
                return false;
            }
        }

        if(!mysqli_stmt_execute($stmt)){
            mysqli_stmt_close($stmt);
            return false;
        }

        return $stmt;
    }
}

if(!function_exists('db_select_one')){
    function db_select_one(mysqli $conn, string $sql, string $types = '', array $params = []): ?array
    {
        $stmt = db_prepared_query($conn, $sql, $types, $params);

        if(!$stmt){
            return null;
        }

        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        return $row ?: null;
    }
}

if(!function_exists('db_select_all')){
    function db_select_all(mysqli $conn, string $sql, string $types = '', array $params = []): array
    {
        $stmt = db_prepared_query($conn, $sql, $types, $params);

        if(!$stmt){
            return [];
        }

        $result = mysqli_stmt_get_result($stmt);
        $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
        mysqli_stmt_close($stmt);

        return $rows;
    }
}

if(!function_exists('db_execute')){
    function db_execute(mysqli $conn, string $sql, string $types = '', array $params = []): bool
    {
        $stmt = db_prepared_query($conn, $sql, $types, $params);

        if(!$stmt){
            return false;
        }

        mysqli_stmt_close($stmt);
        return true;
    }
}

$failedLoginAttemptsColumn = mysqli_query($conn, "SHOW COLUMNS FROM user_auth LIKE 'failed_login_attempts'");
if($failedLoginAttemptsColumn instanceof mysqli_result && mysqli_num_rows($failedLoginAttemptsColumn) === 0){
    mysqli_query($conn, "ALTER TABLE user_auth ADD COLUMN failed_login_attempts INT(11) NOT NULL DEFAULT 0 AFTER otp_expiry");
}

$requireOtpUntilColumn = mysqli_query($conn, "SHOW COLUMNS FROM user_auth LIKE 'require_otp_until'");
if($requireOtpUntilColumn instanceof mysqli_result && mysqli_num_rows($requireOtpUntilColumn) === 0){
    mysqli_query($conn, "ALTER TABLE user_auth ADD COLUMN require_otp_until DATETIME DEFAULT NULL AFTER failed_login_attempts");
}

$complaintTrackingColumn = mysqli_query($conn, "SHOW COLUMNS FROM complaints LIKE 'tracking_number'");
if($complaintTrackingColumn instanceof mysqli_result && mysqli_num_rows($complaintTrackingColumn) === 0){
    mysqli_query($conn, "ALTER TABLE complaints ADD COLUMN tracking_number VARCHAR(30) DEFAULT NULL AFTER complaint_id");
}

mysqli_query($conn, "UPDATE complaints
SET tracking_number = CONCAT('CMP-', DATE_FORMAT(created_at, '%Y%m%d'), '-', LPAD(complaint_id, 5, '0'))
WHERE tracking_number IS NULL
OR tracking_number = ''");

$complaintTrackingIndex = mysqli_query($conn, "SHOW INDEX FROM complaints WHERE Key_name='tracking_number'");
if($complaintTrackingIndex instanceof mysqli_result && mysqli_num_rows($complaintTrackingIndex) === 0){
    mysqli_query($conn, "ALTER TABLE complaints ADD UNIQUE KEY tracking_number (tracking_number)");
}

$uniqueUserTables = [
    'user_auth' => 'unique_user_auth_user',
    'user_profiles' => 'unique_user_profiles_user',
    'residency' => 'unique_residency_user',
    'password_resets' => 'unique_password_resets_user',
];
foreach($uniqueUserTables as $table => $indexName){
    $uniqueUserIndex = mysqli_query($conn, "SHOW INDEX FROM `$table` WHERE Column_name='user_id' AND Non_unique=0");
    if($uniqueUserIndex instanceof mysqli_result && mysqli_num_rows($uniqueUserIndex) === 0){
        $duplicateUserRows = mysqli_query($conn, "SELECT user_id
        FROM `$table`
        WHERE user_id IS NOT NULL
        GROUP BY user_id
        HAVING COUNT(*) > 1
        LIMIT 1");
        if($duplicateUserRows instanceof mysqli_result && mysqli_num_rows($duplicateUserRows) === 0){
            mysqli_query($conn, "ALTER TABLE `$table` ADD UNIQUE KEY `$indexName` (`user_id`)");
        }
    }
}

$profileExtraColumns = [
    'age' => "ALTER TABLE user_profiles ADD COLUMN age INT(3) DEFAULT NULL AFTER phone",
    'gender' => "ALTER TABLE user_profiles ADD COLUMN gender VARCHAR(50) DEFAULT NULL AFTER age",
    'civil_status' => "ALTER TABLE user_profiles ADD COLUMN civil_status VARCHAR(50) DEFAULT NULL AFTER gender",
    'signature_image' => "ALTER TABLE user_profiles ADD COLUMN signature_image VARCHAR(255) DEFAULT NULL AFTER profile_image",
];
foreach($profileExtraColumns as $columnName => $alterSql){
    $profileColumn = mysqli_query($conn, "SHOW COLUMNS FROM user_profiles LIKE '$columnName'");
    if($profileColumn instanceof mysqli_result && mysqli_num_rows($profileColumn) === 0){
        mysqli_query($conn, $alterSql);
    }
}

$complaintResolutionColumn = mysqli_query($conn, "SHOW COLUMNS FROM complaints LIKE 'resolution_confirmation'");
if($complaintResolutionColumn instanceof mysqli_result && mysqli_num_rows($complaintResolutionColumn) === 0){
    mysqli_query($conn, "ALTER TABLE complaints ADD COLUMN resolution_confirmation ENUM('pending','confirmed','reopened') DEFAULT NULL AFTER status");
}

$complaintStatusColumn = mysqli_query($conn, "SHOW COLUMNS FROM complaints LIKE 'status'");
if($complaintStatusColumn instanceof mysqli_result){
    $statusColumn = mysqli_fetch_assoc($complaintStatusColumn);

    if($statusColumn && stripos($statusColumn['Type'], 'enum(') === 0 && strpos($statusColumn['Type'], "'Cancelled'") === false){
        mysqli_query($conn, "ALTER TABLE complaints MODIFY status ENUM('Pending','In Progress','Resolved','Cancelled') NOT NULL DEFAULT 'Pending'");
    }
}

mysqli_query($conn, "UPDATE complaints
SET resolution_confirmation='confirmed'
WHERE status='Resolved'
AND resolution_confirmation IS NULL");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS complaint_updates (
    update_id INT(11) NOT NULL AUTO_INCREMENT,
    complaint_id INT(11) NOT NULL,
    actor_user_id INT(11) DEFAULT NULL,
    actor_role VARCHAR(50) DEFAULT NULL,
    update_type VARCHAR(50) DEFAULT NULL,
    status_snapshot VARCHAR(50) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (update_id),
    KEY complaint_id (complaint_id),
    KEY actor_user_id (actor_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

$complaintUpdateProofPathColumn = mysqli_query($conn, "SHOW COLUMNS FROM complaint_updates LIKE 'proof_path'");
if($complaintUpdateProofPathColumn instanceof mysqli_result && mysqli_num_rows($complaintUpdateProofPathColumn) === 0){
    mysqli_query($conn, "ALTER TABLE complaint_updates ADD COLUMN proof_path VARCHAR(255) DEFAULT NULL AFTER message");
}

$complaintUpdateProofNameColumn = mysqli_query($conn, "SHOW COLUMNS FROM complaint_updates LIKE 'proof_original_name'");
if($complaintUpdateProofNameColumn instanceof mysqli_result && mysqli_num_rows($complaintUpdateProofNameColumn) === 0){
    mysqli_query($conn, "ALTER TABLE complaint_updates ADD COLUMN proof_original_name VARCHAR(255) DEFAULT NULL AFTER proof_path");
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS complaint_update_attachments (
    attachment_id INT(11) NOT NULL AUTO_INCREMENT,
    update_id INT(11) NOT NULL,
    stored_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) DEFAULT NULL,
    file_size INT(11) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (attachment_id),
    KEY update_id (update_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS blotter_reports (
    report_id INT(11) NOT NULL AUTO_INCREMENT,
    complaint_id INT(11) NOT NULL,
    staff_user_id INT(11) DEFAULT NULL,
    complainant_user_id INT(11) DEFAULT NULL,
    admin_user_id INT(11) DEFAULT NULL,
    status ENUM('awaiting_complainant_signature','signed_by_complainant','submitted_to_admin','approved','rejected') NOT NULL DEFAULT 'awaiting_complainant_signature',
    report_path VARCHAR(255) NOT NULL,
    report_original_name VARCHAR(255) NOT NULL,
    report_data LONGTEXT DEFAULT NULL,
    staff_signature_image VARCHAR(255) DEFAULT NULL,
    complainant_signature_image VARCHAR(255) DEFAULT NULL,
    admin_signature_image VARCHAR(255) DEFAULT NULL,
    admin_remarks TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (report_id),
    KEY complaint_id (complaint_id),
    KEY staff_user_id (staff_user_id),
    KEY complainant_user_id (complainant_user_id),
    KEY admin_user_id (admin_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

$blotterReportDataColumn = mysqli_query($conn, "SHOW COLUMNS FROM blotter_reports LIKE 'report_data'");
if($blotterReportDataColumn instanceof mysqli_result && mysqli_num_rows($blotterReportDataColumn) === 0){
    mysqli_query($conn, "ALTER TABLE blotter_reports ADD COLUMN report_data LONGTEXT DEFAULT NULL AFTER report_original_name");
}

mysqli_query($conn, "INSERT INTO complaint_update_attachments (
    update_id,
    stored_path,
    original_name,
    file_type,
    file_size,
    created_at
)
SELECT
    complaint_updates.update_id,
    complaint_updates.proof_path,
    COALESCE(NULLIF(complaint_updates.proof_original_name, ''), SUBSTRING_INDEX(complaint_updates.proof_path, '/', -1)),
    LOWER(SUBSTRING_INDEX(complaint_updates.proof_path, '.', -1)),
    NULL,
    complaint_updates.created_at
FROM complaint_updates
WHERE complaint_updates.proof_path IS NOT NULL
AND complaint_updates.proof_path != ''
AND NOT EXISTS (
    SELECT 1
    FROM complaint_update_attachments
    WHERE complaint_update_attachments.update_id = complaint_updates.update_id
    AND complaint_update_attachments.stored_path = complaint_updates.proof_path
)");

mysqli_query($conn, "INSERT INTO complaint_updates (
    complaint_id,
    actor_user_id,
    actor_role,
    update_type,
    status_snapshot,
    message,
    created_at
)
SELECT
    complaints.complaint_id,
    complaints.complainant_id,
    'complainant',
    'submitted',
    'Pending',
    'Complaint submitted by complainant.',
    complaints.created_at
FROM complaints
WHERE NOT EXISTS (
    SELECT 1
    FROM complaint_updates
    WHERE complaint_updates.complaint_id = complaints.complaint_id
)");

mysqli_query($conn, "DELETE duplicate_updates
FROM complaint_updates duplicate_updates
INNER JOIN complaint_updates original_updates
    ON duplicate_updates.update_id > original_updates.update_id
    AND duplicate_updates.complaint_id = original_updates.complaint_id
    AND (duplicate_updates.actor_user_id <=> original_updates.actor_user_id)
    AND duplicate_updates.actor_role = original_updates.actor_role
    AND duplicate_updates.update_type = original_updates.update_type
    AND duplicate_updates.status_snapshot = original_updates.status_snapshot
    AND duplicate_updates.message = original_updates.message
    AND DATE_FORMAT(duplicate_updates.created_at, '%Y-%m-%d %H:%i') = DATE_FORMAT(original_updates.created_at, '%Y-%m-%d %H:%i')
WHERE duplicate_updates.update_type='assigned'");

$complaintUpdatesComplaintFk = mysqli_query($conn, "SELECT CONSTRAINT_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'complaint_updates'
AND COLUMN_NAME = 'complaint_id'
AND REFERENCED_TABLE_NAME = 'complaints'
LIMIT 1");
if($complaintUpdatesComplaintFk instanceof mysqli_result && mysqli_num_rows($complaintUpdatesComplaintFk) === 0){
    $complaintUpdatesComplaintOrphans = mysqli_query($conn, "SELECT complaint_updates.update_id
    FROM complaint_updates
    LEFT JOIN complaints ON complaints.complaint_id = complaint_updates.complaint_id
    WHERE complaints.complaint_id IS NULL
    LIMIT 1");
    if($complaintUpdatesComplaintOrphans instanceof mysqli_result && mysqli_num_rows($complaintUpdatesComplaintOrphans) === 0){
        mysqli_query($conn, "ALTER TABLE complaint_updates
        ADD CONSTRAINT fk_complaint_updates_complaint
        FOREIGN KEY (complaint_id) REFERENCES complaints (complaint_id)
        ON DELETE CASCADE");
    }
}

$complaintUpdatesActorFk = mysqli_query($conn, "SELECT CONSTRAINT_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'complaint_updates'
AND COLUMN_NAME = 'actor_user_id'
AND REFERENCED_TABLE_NAME = 'users'
LIMIT 1");
if($complaintUpdatesActorFk instanceof mysqli_result && mysqli_num_rows($complaintUpdatesActorFk) === 0){
    $complaintUpdatesActorOrphans = mysqli_query($conn, "SELECT complaint_updates.update_id
    FROM complaint_updates
    LEFT JOIN users ON users.user_id = complaint_updates.actor_user_id
    WHERE complaint_updates.actor_user_id IS NOT NULL
    AND users.user_id IS NULL
    LIMIT 1");
    if($complaintUpdatesActorOrphans instanceof mysqli_result && mysqli_num_rows($complaintUpdatesActorOrphans) === 0){
        mysqli_query($conn, "ALTER TABLE complaint_updates
        ADD CONSTRAINT fk_complaint_updates_actor
        FOREIGN KEY (actor_user_id) REFERENCES users (user_id)
        ON DELETE SET NULL");
    }
}

$complaintAttachmentsUpdateFk = mysqli_query($conn, "SELECT CONSTRAINT_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'complaint_update_attachments'
AND COLUMN_NAME = 'update_id'
AND REFERENCED_TABLE_NAME = 'complaint_updates'
LIMIT 1");
if($complaintAttachmentsUpdateFk instanceof mysqli_result && mysqli_num_rows($complaintAttachmentsUpdateFk) === 0){
    $attachmentOrphans = mysqli_query($conn, "SELECT complaint_update_attachments.attachment_id
    FROM complaint_update_attachments
    LEFT JOIN complaint_updates ON complaint_updates.update_id = complaint_update_attachments.update_id
    WHERE complaint_updates.update_id IS NULL
    LIMIT 1");
    if($attachmentOrphans instanceof mysqli_result && mysqli_num_rows($attachmentOrphans) === 0){
        mysqli_query($conn, "ALTER TABLE complaint_update_attachments
        ADD CONSTRAINT fk_complaint_update_attachments_update
        FOREIGN KEY (update_id) REFERENCES complaint_updates (update_id)
        ON DELETE CASCADE");
    }
}
?>
