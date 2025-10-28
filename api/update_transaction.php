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
    
    if (!isset($input['id']) || (!isset($input['qty']) && !isset($input['price']))) {
        throw new Exception('Missing required fields');
    }
    
    // Build update SQL based on what's being updated
    $updates = [];
    $params = [];
    $types = 's'; // First param is always id (string)
    
    if (isset($input['qty'])) {
        $updates[] = 'qty = ?';
        $params[] = $input['qty'];
        $types .= 'd';
    }
    
    if (isset($input['price'])) {
        $updates[] = 'price = ?';
        $params[] = $input['price'];
        $types .= 'd';
    }
    
    $sql = "UPDATE transactions SET " . implode(', ', $updates) . " WHERE id = ?";
    
    // Add id as last param
    $params[] = $input['id'];
    
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        throw new Exception(mysqli_error($link));
    }
    
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }
    
    if (mysqli_affected_rows($link) === 0) {
        throw new Exception('Transaction not found');
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction updated'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($link);