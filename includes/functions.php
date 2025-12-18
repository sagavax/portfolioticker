<?php
    global $link;
    function GetCountNotes($transaction_id) {
        global $link;
        $get_notes = "SELECT Count(*) as note_count from transaction_notes WHERE transaction_id=$transaction_id";
        $result = mysqli_query($link, $get_notes) or die("MySQLi ERROR: ".mysqli_error($link));
        $row = mysqli_fetch_array($result);
        return $row['note_count'];
    }

    function GetCountPosiotions(){
        global $link;
        $get_positions = "SELECT Count(*) as position_count from transactions";
        $result = mysqli_query($link, $get_positions) or die("MySQLi ERROR: ".mysqli_error($link));
        $row = mysqli_fetch_array($result);
        return $row['position_count'];
    }


    function GetPortfolioValue(){
        global $link;
        $get_positions = "SELECT SUM(qty*price) as portfolio_value from transactions";
        $result = mysqli_query($link, $get_positions) or die("MySQLi ERROR: ".mysqli_error($link));
        $row = mysqli_fetch_array($result);
        return floor($row['portfolio_value']);
    }


    function GetAllTransationsAsset(){
    
        global $link;


    header('Content-Type: application/json');

    $response_data = [];
    
    
    $get_asset = "SELECT DISTINCT symbol FROM transactions";
    $result = mysqli_query($link, $get_asset) or die("MySQL ERROR: " . mysqli_error($link));

    if ($result) {
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            // Add each symbol to the PHP array
            $response_data[] = $row['symbol'];
        }
        // Free result set
        mysqli_free_result($result);
    } else {
        // Handle error (optional, depending on your error handling strategy)
        // In a real application, you'd log this and return a 500 Internal Server Error
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . mysqli_error($link)]);
        exit;
    }

    // Encode the entire array into a single, valid JSON string and echo it
    echo json_encode($response_data);
    }


    function assetCharList(){
          //char list
          foreach (range('A', 'Z') as $char) {
                    echo "<button type='button' name='letterButton' class='button small_button'>$char</button>";
                }
    }


    function assetSymbolList(){
        //symbol list
        global $link;
        $get_tickers = "SELECT DISTINCT ticker FROM tickers WHERE LEFT(ticker, 1) = 'A' ORDER BY ticker ASC LIMIT 100";
        $result = mysqli_query($link, $get_tickers) or die("MySQL ERROR: " . mysqli_error($link));
        while ($row = mysqli_fetch_array($result)) {
            $symbol = $row['ticker'];
            echo '<button name="symbol" class="secondary">'.$symbol.'</button>';
        }
    }


    function assetSymbolListPagination(){
        
    }