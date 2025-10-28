<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error display, we'll handle them in JSON

// Mark this as an API request for dbconnect.php
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

// $link is already verified in dbconnect.php for API requests

try {
    // Basic query with optional filters
    $sql = "SELECT t.*, 
            (SELECT COUNT(*) FROM transaction_notes WHERE transaction_id = t.id) as notes_count
            FROM transactions t";
    
    // Add WHERE conditions based on filters
    $where = [];
    $params = [];
    
    if (!empty($_GET['symbol'])) {
        $where[] = "t.symbol = ?";
        $params[] = $_GET['symbol'];
    }
    
    if (!empty($_GET['type'])) {
        $where[] = "t.type = ?";
        $params[] = $_GET['type'];
    }
    
    if (!empty($_GET['from_date'])) {
        $where[] = "t.date >= ?";
        $params[] = $_GET['from_date'];
    }
    
    if (!empty($_GET['to_date'])) {
        $where[] = "t.date <= ?";
        $params[] = $_GET['to_date'];
    }
    
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    // Always sort by date desc
    $sql .= " ORDER BY t.date DESC";
    
    // Prepare and execute
    $stmt = mysqli_prepare($link, $sql);
    if ($params) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Fetch all rows
    $transactions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Convert numeric strings to proper types
        $row['qty'] = $row['qty'] ? (float)$row['qty'] : null;
        $row['price'] = $row['price'] ? (float)$row['price'] : null;
        $row['notes_count'] = (int)$row['notes_count'];
        $transactions[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $transactions
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

mysqli_close($link);