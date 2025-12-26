<?php

    include_once "includes/dbconnect.php";
    include_once "includes/functions.php";


    

    // get POST data
    $transaction_id = $_POST['transaction_id'] ?? null;
    $date = $_POST['date'] ?? null;
    $provider = $_POST['provider'] ?? null;
    $type = $_POST['type'] ?? null;
    $category = $_POST['category'] ?? null;
    $symbol = $_POST['symbol'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $price = $_POST['price'] ?? null;
    $ccy = $_POST['ccy'] ?? null;


    $update_sql = "UPDATE transactions 
                   SET date = ?, provider = ?, type = ?, category = ?, symbol = ?, qty = ?, price = ?, ccy = ? 
                   WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('ssssddsi', $date, $provider, $type, $category, $symbol, $quantity, $price, $ccy, $transaction_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);