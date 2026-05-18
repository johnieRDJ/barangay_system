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
