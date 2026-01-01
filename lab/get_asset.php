<?php
include "includes/dbconnect.php";
include "includes/functions.php";

header('Content-Type: application/json');

// Inicializujeme asociatívne pole s dvoma kľúčmi: labels a data
$response_data = [
    'labels' => [],
    'data' => []
];

// ZMENA TU: Používame COUNT() a GROUP BY na získanie počtu pre každý symbol
$get_asset = "SELECT symbol, COUNT(symbol) as count FROM transactions GROUP BY symbol ORDER BY symbol ASC";
$result = mysqli_query($link, $get_asset) or die("MySQL ERROR: " . mysqli_error($link));

if ($result) {
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        // Pridávame symbol do poľa 'labels'
        $response_data['labels'][] = $row['symbol'];
        
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

// mysqli_close($link); 
?>