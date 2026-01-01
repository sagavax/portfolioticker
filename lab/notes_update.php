<?php

    include_once "includes/dbconnect.php";
    include_once "includes/functions.php";


    $transaction_id = $_POST['transaction_id'] ?? null;
    $note_text = $_POST['note_text'] ?? '';
    $note_id = $_POST['note_id'] ?? null;


    // update existing note
    if ($note_id) { 

        $update_note_sql = "UPDATE transaction_notes SET text = ?, modified_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_note_sql);
        $stmt->bind_param('si', $note_text, $note_id);
        $stmt->execute();
        $stmt->close();

    // insert new note
    } else {    

        $insert_note_sql = "INSERT INTO transaction_notes (transaction_id, text, created_at, modified_at) VALUES (?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($insert_note_sql);
        $stmt->bind_param('is', $transaction_id, $note_text);
        $stmt->execute();
        $stmt->close();

    }

    echo json_encode(['success' => true]);
    