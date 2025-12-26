<?php

    include_once ("includes/dbconnect.php");
    include_once ("includes/functions.php");

    $count = 0;

    $sql = "SELECT COUNT(*) as total FROM transactions";
    $result = mysqli_query($link, $sql);
    
    if ($result === false) {
        error_log('Query error: ' . mysqli_error($link));
        echo '0';
        exit;
    }
    
    $row = mysqli_fetch_assoc($result);
    $count = $row['total'];

    echo $count;
?>
