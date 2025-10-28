<?php
header('Content-Type: application/json');
require_once '../includes/dbcoonnect.php';

try {
    // Validate required fields
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !isset($input['date']) || !isset($input['provider']) || !isset($input['type'])) {
        throw new Exception('Missing required fields');
    }
    
    // Prepare statement
    $sql = "INSERT INTO transactions (id, date, provider, type, symbol, qty, price, ccy) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        throw new Exception(mysqli_error($link));
    }
    
    // Bind parameters
    mysqli_stmt_bind_param($stmt, 'ssssddds',
        $input['id'],
        $input['date'],
        $input['provider'],
        $input['type'],
        $input['symbol'] ?? null,
        $input['qty'] ?? null,
        $input['price'] ?? null,
        $input['ccy'] ?? 'EUR'
    );
    
    // Execute
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction saved'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($link);