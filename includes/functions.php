<?php

    function GetCountNotes($transaction_id) {
        global $link;
        $get_notes = "SELECT Count(*) as note_count from transaction_notes WHERE transaction_id=$transaction_id";
        $result = mysqli_query($link, $get_notes) or die("MySQLi ERROR: ".mysqli_error($link));
        $row = mysqli_fetch_array($result);
        return $row['note_count'];
    }