<?php

    include_once 'includes/dbconnect.php';
    include_once 'includes/functions.php';

    header('Content-Type: application/json');

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
        $filter = "";
    }


    try {
        $filter_sql = "SELECT * FROM transactions WHERE type = ? ORDER BY date DESC";
        $stmt = $conn->prepare($filter_sql);
        $stmt->bind_param('s', $filter);
        $stmt->execute();
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
