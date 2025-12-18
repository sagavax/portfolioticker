<?php
    include("includes/dbconnect.php");
    include("includes/functions.php");

    //if(!isset($_SESSION['login'])) {
    //header('location:login.php');
    //}
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
     <link rel="stylesheet" href="css/style.css?<?php echo time() ?>" />
     <!-- <link rel="stylesheet" href="css/message.css?<?php echo time() ?>" /> -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
     <link rel="icon" type="image/png" sizes="32x32" href="investment.png">
     <script src="js/analysis.js?<?php echo time() ?>`" defer></script>
     <script src="js/clock.js?<?php echo time() ?>" defer></script>
</head>
<body>
     <header>
         <a href="."><img src="portfolio-ticker-logo.svg" alt="Portfolio Ticker"></a><div class="clockWrapper"><button type ="button" class="secondary" name="worldclock"  id="worldclock">World Clock</button><div id="clock">--:--:--</div></div>
     </header>

     <div class="card">
       <h3>Add new analysis</h3>
       <div id="newAnalysisContent">
          <button id="newAnalysis" type="button" ckass="secondary"><i class="fas fa-plus"></i>Vytvorit</button>
       </div>
     </div>

     <div class="card">
      <h3>Analysis</h3>
        <?php
          $sql = "SELECT * FROM analyses ORDER BY id DESC";
          $result = $link->query($sql) or die($link->error);
          if ($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  echo '<div class="analysis-item">';
                  echo '<h3>' . $row["ticker"] . '</h3>';
                  echo '<p>' . $row["analysis"] . '</p>';
                  echo '</div>';
              }
          } else {
              echo "0 results";
          }
        ?>
     </div>

  <div id="modalNewAnalysis" class="modal-overlay">
  <div class="modal-container">
   <h3>Add a new analysis</h3>
    <div id="modaNerwAnalysisContent">
      <input type="text" id="ticker" placeholder="Ticker" autocomplete="off">
      <textarea id="analysis_text" placeholder="Write your analysis here..."></textarea>
    </div>
    <div class="modal-actions">
      <button id="modalNewAnalysisSave" type="button" ckass="secondary">Uložiť</button>
      <button id="modalNewAnalysisClose" class="secondary">Zatvoriť</button>
    </div>
  </div>
</div>
</body>
</html>