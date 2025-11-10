<?php
    include_once 'includes/dbconnect.php';
    include_once 'includes/functions.php';


    $date = $_POST['date'] ?? null;
    $provider = $_POST['provider'] ?? null;
    $type = $_POST['type'] ?? null;
    $symbol = $_POST['symbol'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $price = $_POST['price'] ?? null;
    $ccy = $_POST['ccy'] ?? null;


    $insert_sql = "INSERT INTO transactions (date, provider, type, symbol, qty, price, ccy) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $link->prepare($insert_sql);
    $stmt->bind_param('ssssdds', $date, $provider, $type, $symbol, $quantity, $price, $ccy);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
?>