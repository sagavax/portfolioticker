<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset detailed information</title>
    <link rel="stylesheet" href="css/style.css?<?php echo time() ?>" />
    <link rel="stylesheet" href="css/asset.css?<?php echo time() ?>" />
    <script src="js/clock.js?<?php echo time() ?>" defer></script>
    <script src="js/assets.js?<?php echo time() ?>" defer></script>
    <link rel="icon" type="image/png" sizes="32x32" href="investment.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
     <link href='https://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
</head>
<body>
    <header>
      <a href="."><img src="portfolio-ticker-logo.svg" alt="Portfolio Ticker"></a><div class="clockWrapper"><button type ="button" class="secondary" name="worldclock"  id="worldclock">World Clock</button><div id="clock">--:--:--</div></div>
    </header>

    <div class="card" id="asset_detail">
        <h3>Asset detailed information</h3>
        <div id="asset_info">
            <?php
                include('includes/dbconnect.php');
                include('includes/functions.php');

                if(isset($_GET['ticker'])) {
                    $ticker = mysqli_real_escape_string($link, $_GET['ticker']);
                    $query = "SELECT * FROM assets WHERE ticker = '$ticker'";
                    $result = mysqli_query($link, $query);
                    if(mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        echo '<h2>'.$row['company_name'].' ('.$row['ticker'].')</h2>';
                        echo '<p><strong>Short Name:</strong> '.$row['short_name'].'</p>';
                        echo '<p><strong>Industry:</strong> '.$row['industry'].'</p>';
                        echo '<p><strong>Website:</strong> <a href="'.$row['website'].'" target="_blank">'.$row['website'].'</a></p>';
                        echo '<p><strong>Description:</strong> '.$row['description'].'</p>';
                    } else {
                        echo '<p>No information found for the specified ticker.</p>';
                    }
                } else {
                    echo '<p>No ticker specified.</p>';
                }

                mysqli_close($link);
            ?>
        </div>
    </div>
    <div class="card" id="asset_transaction_history">
        <h3>Transaction History for <?php echo isset($ticker) ? $ticker : ''; ?></h3>
        <div id="transaction_list">
            <?php
                include('includes/dbconnect.php');

                if(isset($ticker)) {
                    $query = "SELECT * FROM transactions WHERE ticker = '$ticker' ORDER BY date DESC";
                    $result = mysqli_query($link, $query);
                    if(mysqli_num_rows($result) > 0) {
                        echo '<table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>';
                        while($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>
                                    <td>'.$row['date'].'</td>
                                    <td>'.$row['type'].'</td>
                                    <td>'.$row['quantity'].'</td>
                                    <td>'.$row['price'].'</td>
                                  </tr>';
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<p>No transactions found for this asset.</p>';
                    }
                }

                mysqli_close($link);
            ?>
        </div>
    </div><!-- end of asset transaction history -->

    
    <div class="card">
        <a href="assets.php" class="button primary">Back to Assets List</a>
    </div>        
</body>
</html>