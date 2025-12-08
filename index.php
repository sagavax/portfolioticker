<?php
  include('includes/dbconnect.php');
  include('includes/functions.php');

  session_start();

  if(!isset($_SESSION['user_id'])) {
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
     <link rel="stylesheet" href="css/message.css?<?php echo time() ?>" />
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
     <link rel="icon" type="image/png" sizes="32x32" href="investment.png">
     <script type="module" src="js/main.js?<?php echo time() ?>"></script>
</head>
<body>
    <header>
    <h1>Portfolio Ticker</h1>
    <span class="muted">vanilla JS + localStorage</span>
  </header>

  <section class="grid">
    <div class="card">
      <div class="muted">Hodnota portfólia</div>
      <div id="portfolioValue" style="font-size:24px;font-weight:700">—</div>
      <div id="portfolioPnL" class="muted">P/L: —</div>
    </div>
    <div class="card">
      <div class="muted">Počet pozícií</div>
      <div id="positionsCount" style="font-size:24px;font-weight:700">—</div>
      <div id="cashInfo" class="muted">Hotovosť: —</div>
    </div>
  </section>

  <section class="card">
    <h2 style="margin-top:0">Nová transakcia</h2>
    <form id="txForm" class="row">
      <label>Dátum<input type="date" name="date" required></label>
      <label>Provider
        <select name="provider" required>
          <option value="Robinhood">Robinhood</option>
          <option value="Binance">Binance</option>
          <option value="Binance">Bybit</option>
          <option value="Pionex">Pionex</option>
          <option value="XTB">XTB</option>
          <option value="eToro">eToro</option>
          <option value="Interactive Brokers">Interactive Brokers</option>
          <option value="Trading 212">Trading 212</option>
          <option value="Revolut">Revolut</option>
          <option value="DEGIRO">DEGIRO</option>
          <option value="CSOB">Csob</option>
          <option value="365.banka">365.banka</option>
          <option value="Tatra Banka">Tatra Banka</option>
          <option value="mBank">mBank</option>
          <option value="Slovenska sporitelna">Slovenska sporitelna</option>
        </select>
      </label>
      <label>Typ
        <select name="type">
          <option value="BUY">BUY</option>
          <option value="SELL">SELL</option>
          <option value="DEPOSIT">DEPOSIT</option>
          <option value="WITHDRAW">WITHDRAW</option>
          <option value="DIVIDEND">DIVIDEND</option>
          <option value="FEE">FEE</option>
        </select>
      </label>
      <label>Kategória
        <select name="category">
          <option value="STOCKS">Akcie</option>
          <option value="ETF">ETF</option>
          <option value="FUND">Fondy</option>
          <option value="BOND">Dlhopisy</option>
          <option value="CRYPTO">Crypto</option>
        </select>
      </label>
      <label>Ticker <input name="symbol" placeholder="AAPL, MSFT..." /></label>
      <label>Množstvo <input type="number" step="0.000001" name="qty" value="0"></label>
      <label>Cena / ks <input type="number" step="0.000001" name="price" value="0"></label>
      <!-- <label>Poplatok <input type="number" step="0.000001" name="fee" value="0"></label> -->
      <label>Mena
        <select name="ccy">
          <option>EUR</option><option>USD</option><option>GBP</option><option>CZK</option>
        </select>
      </label>
      <button type="submit"><i class='far fa-plus-square'></i> Pridať</button>
    </form>
  </section>

<!--  <section class="card">
    <div class="row" style="justify-content:space-between">
      <h2 style="margin:0">Pozície</h2>
      <div class="row">
        <button id="exportBtn" class="secondary">Export CSV</button>
        <label class="secondary">Import CSV <input type="file" id="importFile" accept=".csv" /></label>
      </div>
    </div>
    <table id="positionsTable">
      <thead>
        <tr>
          <th>Ticker</th><th>Množstvo</th><th>Priem. cena</th><th>Aktuálna cena*</th>
          <th>Hodnota</th><th>P/L</th><th class="right">Akcie</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    <div class="muted">* Aktuálne ceny sú demo (manuálne/uložené). Neskôr pripni API.</div>
  </section> -->

  <section class="card">
    <h2 style="margin-top:0">Filter</h2>
    <div class= "transactionsFilter">
      <button name="StocksFilter" class="secondary">Akcie</button><button name="FundsFilter" class="secondary">Fondy</button></button><button name="BondsFilter" class="secondary">Dlhopisy</button><button name="cryptoFilter" class="secondary">Crypto</button><button name="AllFilter" class="secondary">Všetko</button>
    </div>
  </section> 

  <section class="card">
    <h2 style="margin-top:0">Transakcie</h2>
    <table id="transactionsTable">
      <thead>
        <tr>
          <th>Ticker</th><th>Typ</th><th>Kategória</th><th>Množstvo</th><th>Cena</th><th>Poplatok</th><th>Mena</th><th>Dátum</th>
        <th>Poznámky</th><th><div class="right">Akcie</div></th></tr>
      </thead>
      <tbody>
        <?php
            $get_transactions = "SELECT * FROM transactions ORDER BY created_at DESC";
            //echo $get_transactions;
            $result = mysqli_query($link, $get_transactions) or die("MySQL ERROR: " . mysqli_error($link));
            while ($row = mysqli_fetch_array($result)) {
              $idx = $row['id'];  
              $symbol = $row['symbol'];
                $type = $row['type'];
                $category = $row['category'];
                $qty = $row['qty'];
                $price = $row['price'];
                $fee = $row['fee'];
                $ccy = $row['ccy'];
                $created_at = $row['created_at'];
                echo "<tr><td>$symbol</td><td>$type</td><td>$category</td><td class='editable-cell' contenteditable='true' data-id='$idx'>$qty</td><td contenteditable='true' class='editable-cell'data-id='$idx'>$price</td><td>$fee</td><td>$ccy</td><td>$created_at</td><td><span class='note-count' data-id='$idx' title='Poznámky'>".GetCountNotes($idx)."</span></td><td><div style='display:flex; justify-content:flex-end'><button data-note='$idx' data-id='$idx' style='margin-left:8px;' class='secondary'><i class='far fa-plus-square'></i>Pridať poznámku</button><button data-editnote='$idx' data-id='$idx' style='margin-left:8px;' class='secondary'><i class='far fa-edit'></i>Upraviť</button><button data-delnote='$idx' data-id='$idx' style='margin-left:8px;' class='secondary'><i class='far fa-trash-alt'></i>Zmazať</button></div></td></tr>";
            }
        ?>
     
      </tbody>
    </table>
  </section> 
</body>
</html>

<!-- Note modal -->
<div id="noteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:9999;">
  <div style="background-color: #435B7C;border-radius:6px;max-width:600px;width:90%;box-shadow:0 10px 30px rgba(0,0,0,0.2);box-sizing: border-box; padding:10px;">
    <h3 style="margin-top:0">Pridať poznámku</h3>
  <!-- <div id="noteExistingList" style="max-height:160px;overflow:auto;margin-bottom:8px;border:1px solid #f0f0f0;padding:8px;border-radius:6px;background:#fafafa;font-size:13px;color:#222"></div> -->
  <textarea id="noteText" placeholder="Napíš novú poznámku sem..." style="background:#253649;width:100%;height:160px;margin-bottom:8px;padding:8px;font-size:14px;box-sizing:border-box"></textarea>
    <div style="text-align:right;display:flex; justify-content:flex-end">
      <button id="noteCancel" class="secondary" style="margin-right:8px">Zrušiť</button>
      <button id="noteSave">Uložiť</button>
    </div>
  </div>
</div>

<!-- Notes list modal -->
<div id="notesListModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:9999;">
  <div style="background-color: #253649;padding:16px;border-radius:6px;max-width:800px;width:95%;max-height:80vh;overflow:auto;box-shadow:0 10px 30px rgba(0,0,0,0.2);">
    <h3 style="margin-top:0">Zoznam poznámok</h3>
    <div id="notesListContent" style="margin-bottom:12px;font-size:14px;line-height:1.4;"></div>
    <div style="text-align:right">
      <button id="notesListClose" class="secondary">Zatvoriť</button>
    </div>
  </div>
</div>
</html>