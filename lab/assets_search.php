<?php

error_reporting(E_ALL); 
ini_set('display_errors', 1);

    include('includes/dbconnect.php');
    include('includes/functions.php');

       
   $asset = mysqli_real_escape_string($link, $_GET['asset']);


    // *** ZMENA JE V TOMTO RIADKU: PRIDANIE LIMIT 50 ***
    $get_assets = "SELECT * from tickers where ticker LIKE'%$asset%' LIMIT 50";
    
    $result = mysqli_query($link, $get_assets) or die("MySQL ERROR: " . mysqli_error($link));
    
    while ($row = mysqli_fetch_array($result)) {
        $symbol = $row['ticker'];
        echo '<button name="symbol" class="secondary">'.$symbol.'</button>';
    }