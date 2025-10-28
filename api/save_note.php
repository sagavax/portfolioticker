<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Mark this as an API request
define('IS_API_REQUEST', true);

// Check if file exists before require
if (!file_exists('../includes/dbconnect.php')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection file not found'
    ]);
    exit;
}

require_once '../includes/dbconnect.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['transaction_id']) || !isset($input['text'])) {
        throw new Exception('Missing required fields');
    }
    
    // Insert note
    $sql = "INSERT INTO transaction_notes (transaction_id, text) VALUES (?, ?)";
    
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        throw new Exception(mysqli_error($link));
    }
    
    mysqli_stmt_bind_param($stmt, 'ss', $input['transaction_id'], $input['text']);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }
    
    // Get the new note with timestamps
    $noteId = mysqli_insert_id($link);
    $sql = "SELECT id, text, created_at, modified_at FROM transaction_notes WHERE id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $noteId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $note = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Note saved',
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