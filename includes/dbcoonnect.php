<?php
    //connect to database
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "root";
    $dbname = "portfolio_db";
    $link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);


    if (!$link) {
        die("Connection failed: " . mysqli_connect_error());
    }