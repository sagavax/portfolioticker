<?php

    include_once 'includes/dbconnect.php';
    include_once 'includes/functions.php';

    //date=${date}&provider=${provider}&ticker=${ticker}&type=${type}&quantity=${quantity}&price=${price}&ccy=${ccy}`
    $date = $_POST['date'];
    $provider = mysqli_real_escape_string($link,$_POST['provider']);
    $ticker = mysqli_real_escape_string($link,$_POST['ticker']);
    $type = $_POST['type'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price']; //
    $ccy = $_POST['ccy']; //add default EUR

    //$id = generateUUID();

    $add_transaction = "INSERT INTO transactions (date, provider, type, symbol, qty, price, ccy) VALUES ('$date', '$provider', '$type', '$ticker', '$quantity', '$price', '$ccy')";
    mysqli_query($link, $add_transaction) or die("Error inserting transaction: " . mysqli_error($link));

