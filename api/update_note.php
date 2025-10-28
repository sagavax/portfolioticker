<?php
header('Content-Type: application/json');
require_once '../includes/dbcoonnect.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !isset($input['text'])) {
        throw new Exception('Missing required fields');
    }
    
    // Update note
    $sql = "UPDATE transaction_notes SET text = ? WHERE id = ?";
    
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        throw new Exception(mysqli_error($link));
    }
    
    mysqli_stmt_bind_param($stmt, 'si', $input['text'], $input['id']);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }
    
    if (mysqli_affected_rows($link) === 0) {
        throw new Exception('Note not found');
    }
    
    // Get the updated note with timestamps
    $sql = "SELECT id, text, created_at, modified_at FROM transaction_notes WHERE id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $input['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $note = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Note updated',
        'data' => $note
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($link);