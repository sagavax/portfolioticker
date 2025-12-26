<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    //include_once 'includes/dbconnect.php';
    include_once 'includes/functions.php';

    //header('Content-Type: application/json');

    // Validácia vstupov
    $transaction_id = $_POST['transactionId'] ?? null;
    $note = mysqli_real_escape_string($link, $_POST['noteContent']) ?? null;

    $insert_sql = "INSERT INTO transaction_notes (transaction_id, note, modified_at)  VALUES ($transaction_id, '$note', NOW())";
    echo $insert_sql;
    $result = mysqli_query($link, $insert_sql) or die('Insert failed: ' . mysqli_error($link));

  /*   if (!$transaction_id || !is_numeric($transaction_id)) {
        echo json_encode(['success' => false, 'error' => 'Invalid transaction ID']);
        exit;
    }

    if (empty(trim($note))) {
        echo json_encode(['success' => false, 'error' => 'Note cannot be empty']);
        exit;
    }

    // Prepared statement s placeholdermi
    $insert_sql = "INSERT INTO transaction_notes (transaction_id, note, modified_at) 
                   VALUES (?, ?, NOW())";
    
    $stmt = $link->prepare($insert_sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Prepare error: ' . mysqli_error($link)]);
        exit;
    }

    // Naviazanie parametrov
    if (!$stmt->bind_param("is", $transaction_id, $note)) {
        echo json_encode(['success' => false, 'error' => 'Bind error: ' . $stmt->error]);
        exit;
    }
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Execute error: ' . $stmt->error]);
        exit;
    }
*/
    echo json_encode(['success' => true, 'note_id' => $stmt->insert_id]);
    
?>