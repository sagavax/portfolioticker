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
    <title>Portforlio Tracker</title>
     <link rel="stylesheet" href="css/style.css?<?php echo time() ?>" />
     <!-- <link rel="stylesheet" href="css/message.css?<?php echo time() ?>" /> -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
     <link href='https://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
     <link rel="icon" type="image/png" sizes="32x32" href="investment.png">
     <script type="module" src="js/main.js?<?php echo time() ?> defer"></script>
     <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <script src="js/clock.js?<?php echo time() ?>" defer></script>
     <!-- <script src="js/worldclock.js?<?php echo time() ?>"></script> -->
    
</head>
<body>
    <header>
      <a href="."><img src="portfolio-ticker-logo.svg" alt="Portfolio Ticker"></a><div class="clockWrapper"><button type ="button" class="secondary" name="worldclock"  id="worldclock">World Clock</button><div id="clock">--:--:--</div></div>
    </header>



  <section class="grid">
    <div class="card">
      <div class="muted">Menu</div>
      <div class="main-menu" id="main-menu" style="display:flex; flex-direction:column;">
        <button type="button" class="secondary" name="notes" id="notes">Investment</button> 
        <button type="button" class="secondary" name="notes" id="notes">Assets</button>
        <button type="button" class="secondary" name="notes" id="notes">Influencers</button>
        <button type="button" class="secondary" name="notes" id="notes">Analysis</button>
      </div>  
    </div>
    <div class="card">
      
    </div>
  </section>  
</body>
</html>

<!-- Note modal -->
<div id="noteModal" class="modal-overlay">
  <div class="modal-container">
    <h3>Pridať poznámku</h3>
  <!-- <div id="noteExistingList" style="max-height:160px;overflow:auto;margin-bottom:8px;border:1px solid #f0f0f0;padding:8px;border-radius:6px;background:#fafafa;font-size:13px;color:#222"></div> -->
  <textarea id="noteText" placeholder="Napíš novú poznámku sem..." class="modal-textarea"></textarea>
    <div class="modal-actions">
      <button id="noteCancel" class="secondary">Zrušiť</button>
      <button id="noteSave">Uložiť</button>
    </div>
  </div>
</div>

<!-- Notes list modal -->
<div id="notesListModal" class="modal-overlay">
  <div class="modal-container-large">
    <h3>Zoznam poznámok</h3>
    <div id="notesListContent" class="modal-content"></div>
    <div class="modal-actions">
      <button id="notesListClose" class="secondary">Zatvoriť</button>
    </div>
  </div>
</div>
</html>

<div id="assetListModal" class="modal-overlay">
  <div class="modal-container-asset">
    <h3>Zoznam assetu</h3>
    <div id="charList">
      <?php echo assetCharList() ?>
    </div>
    <input id="assetListSearch" placeholder="Hledať...">
    <div id="assetListContent">
      <?php echo assetSymbolList() ?>
    </div>
    <div id="assetListContentPagination">
      <?php echo assetSymbolListPagination() ?>
    </div>
    <div class="modal-actions">
      <button id="assetListClose" class="secondary">Zatvoriť</button>
    </div>
  </div>
</div>
 

<div id="modalModifyPosition" class="modal-overlay">
  <div class="modal-container">
    <h3>Uprava položky</h3>
    <div id="modalModifyPosiotionContent">
        <input type="number" id="modalModifyPosiotionQty" placeholder="Množstvo" autocomplete="off">
        <input type="number" id="modalModifyPosiotionPrice" placeholder="Cena" autocomplete="off">
        <button id="modalModifyPosiotionSave" type="button" ckass="secondary">Uložiť</button>
    </div>
    <div class="modal-actions">
      <button id="modalModifyPosiotionClose" class="secondary">Zatvoriť</button>
    </div>
  </div>
</div>


<div id="modalStopLossTakeProfit" class="modal-overlay">
  <div class="modal-container">
   <h3>Uprava Stop loss a Take profit</h3>
    <div id="modalStopLossTakeProfitContent">
        <input type="number" id="modalStopLoss" placeholder="Stop loss" autocomplete="off">
        <input type="number" id="modalTakeProfit" placeholder="Take profit" autocomplete="off">
        <button id="modalStopLossTakeProfitSave" type="button" ckass="secondary">Uložiť</button>
    </div>
    <div class="modal-actions">
      <button id="modalStopLossTakeProfitClose" class="secondary">Zatvoriť</button>
    </div>
  </div>
</div>

<div id="modalWorldClock" class="modal-overlay">
  <div class="modal-container">
   <h3>World Clock</h3>
    <div id="modalWorldClockContent"></div>
    <div class="modal-actions">
      <button id="modalWorldClockClose" class="secondary">Zatvoriť</button>
    </div>
  </div>
</div>