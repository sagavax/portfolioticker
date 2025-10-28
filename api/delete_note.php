<?php
header('Content-Type: application/json');
require_once '../includes/dbcoonnect.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        throw new Exception('Missing note ID');
    }
    
    // Delete note
    $sql = "DELETE FROM transaction_notes WHERE id = ?";
    
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        throw new Exception(mysqli_error($link));
    }
    
    mysqli_stmt_bind_param($stmt, 'i', $input['id']);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }
    
    if (mysqli_affected_rows($link) === 0) {
        throw new Exception('Note not found');
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Note deleted'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($link);