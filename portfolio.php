<?php
declare(strict_types=1);

// Zapni zobrazovanie chýb
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Nastav JSON header hneď na začiatku
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/includes/dbconnect.php';
    
    if (!isset($link) || !$link) {
        throw new Exception('Database connection failed');
    }
    
    mysqli_set_charset($link, 'utf8mb4');
    
    $sql = "SELECT `id`, `date`, `provider`, `symbol`, `type`, `qty`, `price`, `ccy`
        FROM `transactions`
        ORDER BY `date` DESC, `id` DESC";
    
    $result = mysqli_query($link, $sql);
    
    if ($result === false) {
        throw new Exception('Query error: ' . mysqli_error($link));
    }
    
    // Načítanie všetkých záznamov
    $transactions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[] = $row;
    }
    
    // Uvoľnenie zdrojov
    mysqli_free_result($result);
    mysqli_close($link);
    
    // Korektný JSON output
    echo json_encode([
        'success' => true, 
        'transactions' => $transactions
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
    exit;
}