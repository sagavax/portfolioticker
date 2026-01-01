<?php

    include_once "dbconnect.php";
    include_once "functions.php";

    $transaction_id = $_POST['transaction_id'] ?? null;

    $remove_trensaction_sql = "DELETE FROM `transactions` WHERE `id` = ?";
    $stmt = $link->prepare($remove_trensaction_sql);
    $stmt->bind_param('i', $transaction_id);
    $stmt->execute();
    $stmt->close(); 
    
    echo json_encode(['success' => true]);

?>