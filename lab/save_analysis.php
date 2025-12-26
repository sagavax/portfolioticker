<?php
    include "includes/dbconnect.php";
    include "includes/functions.php";

    $ticker = mysqli_real_escape_string($link,$_POST['ticker']);
    $analysis = mysqli_real_escape_string($link,$_POST['analysis']);

    $save_analysis = "INSERT INTO analyses (ticker, analysis,created_date) VALUES ('$ticker', '$analysis', now())";
    //echo $save_analysis;
    $result = $link->query($save_analysis);
    if (!$result) {
        die("Query failed: " . $link->error);
    }

?>