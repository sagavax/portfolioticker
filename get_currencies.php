<?php


require_once("includes/dbconnect.php");
require_once("includes/functions.php");

header('Content-Type: application/json');

$get_currencies = "SELECT ccy, count(ccy) as count FROM transactions GROUP BY ccy";
$result=mysqli_query($link, $get_currencies) or die(mysqli_error($link));

if ($result) {
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        // Pridávame symbol do poľa 'labels'
        $response_data['labels'][] = $row['ccy'];
        
        // Pridávame počet do poľa 'data' (prevedené na integer pre Chart.js)
        $response_data['data'][] = (int)$row['count'];
    }
    mysqli_free_result($result);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . mysqli_error($link)]);
    exit;
}

// Vrátime finálnu JSON štruktúru
echo json_encode($response_data);
