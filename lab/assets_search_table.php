<?php


    include('includes/dbconnect.php');
    include('includes/functions.php');
     
   $asset = mysqli_real_escape_string($link, $_GET['asset']);


    $get_assets = "SELECT * from tickers where ticker LIKE'%$asset%'";    
    
    $result = mysqli_query($link, $get_assets) or die("MySQL ERROR: " . mysqli_error($link));
    
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