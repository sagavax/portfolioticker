<?php
    // Zapni zobrazovanie chýb pre debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

     include_once 'includes/dbconnect.php';
    include_once 'includes/functions.php';

    header('Content-Type: application/json');

    // Skontroluj či $link existuje
    if (!isset($link)) {
        echo json_encode(['success' => false, 'error' => 'Database connection not established']);
        exit;
    }

    $filter = $_POST['filter'] ?? null;

    if (empty($filter)) {
        echo json_encode(['success' => false, 'error' => 'Filter parameter is required']);
        exit;
    }

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
            $stmt = $link->prepare($filter_sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $link->error);
            }
            
            $stmt->execute();
        } else {
            $filter_sql = "SELECT * FROM transactions WHERE category = ? ORDER BY date DESC";
            $stmt = $link->prepare($filter_sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $link->error);
            }
            
            $stmt->bind_param('s', $filter);
            $stmt->execute();
        }
        
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode([
            'success' => true,
            'transactions' => $transactions
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
?>