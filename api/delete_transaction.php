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
    
    if (!isset($input['id'])) {
        throw new Exception('Missing transaction ID');
    }
    
    // Start transaction to ensure both transaction and its notes are deleted
    mysqli_begin_transaction($link);
    
    try {
        // Delete transaction (notes will be cascade deleted due to FK constraint)
        $sql = "DELETE FROM transactions WHERE id = ?";
        
        $stmt = mysqli_prepare($link, $sql);
        if (!$stmt) {
            throw new Exception(mysqli_error($link));
        }
        
        mysqli_stmt_bind_param($stmt, 's', $input['id']);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }
        
        if (mysqli_affected_rows($link) === 0) {
            throw new Exception('Transaction not found');
        }
        
        // Commit if all good
        mysqli_commit($link);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Transaction deleted'
        ]);
        
    } catch (Exception $e) {
        // Rollback on any error
        mysqli_rollback($link);
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($link);