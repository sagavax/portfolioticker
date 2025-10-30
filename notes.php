<?php

    include_once 'includes/dbconnect.php';
    include_once 'includes/functions.php';


    $transaction_id = $_POST['transaction_id'] ?? null;


    $get_notes_sql = "SELECT id, text, created_at, modified_at 
                      FROM transaction_notes 
                      WHERE transaction_id = ? 
                      ORDER BY created_at DESC";

    echo $get_notes_sql;

    $stmt = $conn->prepare($get_notes_sql);
    $stmt->bind_param('i', $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $notes = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($notes);