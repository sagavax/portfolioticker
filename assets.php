<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);


  include('includes/dbconnect.php');
  include('includes/functions.php');

  session_start();

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

  if(!isset($_SESSION['login'])) {
    header('location:login.php');
  }

  ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css?<?php echo time() ?>" />
    <link rel="stylesheet" href="css/assets.css?<?php echo time() ?>" />
     <!-- <link rel="stylesheet" href="css/message.css?<?php echo time() ?>" /> -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
     <link href='https://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
     <script src="js/clock.js?<?php echo time() ?>" defer></script>
     <script src="js/assets.js?<?php echo time() ?>" defer></script>
     <link rel="icon" type="image/png" sizes="32x32" href="investment.png">
    <title>Assets</title>
</head>
<body>
    <header>
      <a href="."><img src="portfolio-ticker-logo.svg" alt="Portfolio Ticker"></a><input type="text" auutocomplete="off" name="search_asset" placeholder="search the asset here..."><div class="clockWrapper"><button type ="button" class="secondary" name="worldclock"  id="worldclock">World Clock</button><div id="clock">--:--:--</div></div>
    </header>

    <div class="card" id="assets">
        <h3>Assets, detailed information</h3>
        <div class="card" id="asset_header">
            <?php
                //alphabet
                $alphabet = range('A', 'Z');
                foreach ($alphabet as $letter) {
                    echo '<button type="button" class="secindary" data-letter="'.$letter.'">'.$letter.'</button>';
                }
            ?>
        </div>    
        <div class="card" id="asset_list">
            <table>
                <theader>
                <tr>
                    <th>Ticker</th>
                    <th>Company Name</th>
                    <th>Short Name</th>
                    <th>Industry</th>
                    <th>Website</th>
                    <th>Description</th>
                 </tr>
                </theader>
                <tbody>
                    <?php
                        $itemsPerPage = 20;
                        $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
                        $offset = ($current_page - 1) * $itemsPerPage;

                        $get_asset = "SELECT * from tickers ORDER BY ticker ASC LIMIT $itemsPerPage OFFSET $offset";
                        $result = mysqli_query($link, $get_asset) or die(mysqli_error($link));
                        while ($row = mysqli_fetch_array($result)) {
                            $ticker = $row['ticker'];
                            $company_name = $row['company_name'];
                            $short_name = $row['short_name'];
                            $industry = $row['industry'];
                            $description = $row['description'];
                            $website = $row['website'];
                            
                            echo "<tr>";
                                echo "<td>$ticker</td>";
                                echo "<td>$company_name</td>";
                                echo "<td>$short_name</td>";
                                echo "<td>$industry</td>";
                                echo "<td><a href='$website' target='_blank'>$website</a></td>";
                                echo "<td>$description</td>";
                            echo "</tr>";
                        }                    
                    ?>
                </tbody>
            </table>
        </div>
         <?php
            // Calculate the total number of pages
            $sql = "SELECT COUNT(*) as total FROM tickers";
            $result=mysqli_query($link, $sql);
            $row = mysqli_fetch_array($result);
            $totalItems = $row['total'];
            $totalPages = ceil($totalItems / $itemsPerPage);
            
            echo '<div class="pagination">';

                // Tlačidlo PREVIOUS
                if ($current_page > 1) {
                    echo '<a href="?page=' . ($current_page - 1) . '" class="button small_button">« Previous</a>';
                }

                // Info o stránke
                echo '<span style="margin: 0 15px;">Strana ' . $current_page . ' z ' . $totalPages . '</span>';

                // Tlačidlo NEXT
                if ($current_page < $totalPages) {
                    echo '<a href="?page=' . ($current_page + 1) . '" class="button small_button">Next »</a>';
                }

            echo '</div>';
    ?>
                       
    </div>
</body>
</html>