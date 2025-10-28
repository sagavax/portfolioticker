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
    // Get and validate input
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        throw new Exception('No input data provided');
    }
    
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    // Validate required fields
    $requiredFields = ['id', 'date', 'provider', 'type'];
    $missing = array_filter($requiredFields, function($field) use ($input) {
        return !isset($input[$field]) || trim($input[$field]) === '';
    });
    
    if (!empty($missing)) {
        throw new Exception('Missing required fields: ' . implode(', ', $missing));
    }
    
    // Prepare statement
    $sql = "INSERT INTO transactions (id, date, provider, type, symbol, qty, price, ccy) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($link));
    }
    
    // Prepare values with proper type casting
    $id = $input['id'];
    $date = $input['date'];
    $provider = $input['provider'];
    $type = $input['type'];
    $symbol = isset($input['symbol']) ? $input['symbol'] : null;
    $qty = isset($input['qty']) ? floatval($input['qty']) : null;
    $price = isset($input['price']) ? floatval($input['price']) : null;
    $ccy = isset($input['ccy']) ? $input['ccy'] : 'EUR';
    
    // Bind parameters
    if (!mysqli_stmt_bind_param($stmt, 'ssssddds',
        $id, $date, $provider, $type, $symbol, $qty, $price, $ccy
    )) {
        mysqli_stmt_close($stmt);
        throw new Exception('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
    }
    
    // Execute
    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new Exception('Failed to save transaction: ' . $error);
    }
    
    if (mysqli_stmt_affected_rows($stmt) === 0) {
        mysqli_stmt_close($stmt);
        throw new Exception('No transaction was saved');
    }
    
    // Clean up
    mysqli_stmt_close($stmt);
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Transaction saved successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt) && $stmt) {
        mysqli_stmt_close($stmt);
    }
    if (isset($link) && $link) {
        mysqli_close($link);
    }
}