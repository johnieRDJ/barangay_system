<?php

function addComplaintUpdate(
    mysqli $conn,
    int $complaintId,
    ?int $actorUserId,
    string $actorRole,
    string $updateType,
    string $statusSnapshot,
    string $message,
    ?string $proofPath = null,
    ?string $proofOriginalName = null
): ?int
{
    $duplicate = db_select_one(
        $conn,
        "SELECT update_id
         FROM complaint_updates
         WHERE complaint_id=?
         AND (actor_user_id <=> ?)
         AND actor_role=?
         AND update_type=?
         AND status_snapshot=?
         AND message=?
         AND created_at >= (NOW() - INTERVAL 30 SECOND)
         ORDER BY update_id DESC
         LIMIT 1",
        'iissss',
        [
            intval($complaintId),
            $actorUserId,
            $actorRole,
            $updateType,
            $statusSnapshot,
            $message
        ]
    );

    if($duplicate){
        return intval($duplicate['update_id']);
    }

    $stmt = db_prepared_query(
        $conn,
        "INSERT INTO complaint_updates (
            complaint_id,
            actor_user_id,
            actor_role,
            update_type,
            status_snapshot,
            message,
            proof_path,
            proof_original_name
        ) VALUES (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )",
        'iissssss',
        [
            intval($complaintId),
            $actorUserId,
            $actorRole,
            $updateType,
            $statusSnapshot,
            $message,
            $proofPath,
            $proofOriginalName
        ]
    );

    if(!$stmt){
        return null;
    }

    mysqli_stmt_close($stmt);
    return intval(mysqli_insert_id($conn));
}

function addComplaintUpdateAttachment(
    mysqli $conn,
    int $updateId,
    string $storedPath,
    string $originalName,
    string $fileType,
    int $fileSize
): void
{
    $duplicate = db_select_one(
        $conn,
        "SELECT attachment_id
         FROM complaint_update_attachments
         WHERE update_id=?
         AND original_name=?
         AND file_type=?
         AND file_size=?
         AND created_at >= (NOW() - INTERVAL 30 SECOND)
         LIMIT 1",
        'issi',
        [
            $updateId,
            $originalName,
            $fileType,
            $fileSize
        ]
    );

    if($duplicate){
        return;
    }

    db_execute(
        $conn,
        "INSERT INTO complaint_update_attachments (
            update_id,
            stored_path,
            original_name,
            file_type,
            file_size
        ) VALUES (?, ?, ?, ?, ?)",
        'isssi',
        [
            $updateId,
            $storedPath,
            $originalName,
            $fileType,
            $fileSize
        ]
    );
}
?>
