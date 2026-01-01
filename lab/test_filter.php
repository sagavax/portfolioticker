<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'includes/dbconnect.php';

header('Content-Type: application/json');

$filter = 'StocksFilter'; // Simulujeme POST

if($filter === "StocksFilter"){
    $filter = "STOCKS";
} elseif($filter === "FundsFilter"){
    $filter = "FUND";
} elseif($filter === "BondsFilter"){
    $filter = "BOND";
} elseif($filter === "cryptoFilter"){
    $filter = "CRYPTO";
} elseif($filter === "AllFilter"){
    $filter = "ALL";
}

try {
    if ($filter === "ALL") {
        $filter_sql = "SELECT * FROM transactions ORDER BY date DESC";
        $stmt = $conn->prepare($filter_sql);
        $stmt->execute();
    } else {
        $filter_sql = "SELECT * FROM transactions WHERE category = ? ORDER BY date DESC";
        $stmt = $conn->prepare($filter_sql);
        $stmt->bind_param('s', $filter);
        $stmt->execute();
    }
    
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode([
        'success' => true,
        'transactions' => $transactions,
        'filter_used' => $filter,
        'count' => count($transactions)
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
