<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portforlio Tracker</title>
     <link rel="stylesheet" href="css/style.css" />
     <script type="module" src="js/main.js"></script>
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
      <label>Ticker <input name="symbol" placeholder="AAPL, MSFT..." /></label>
      <label>Množstvo <input type="number" step="0.000001" name="qty" value="0"></label>
      <label>Cena / ks <input type="number" step="0.000001" name="price" value="0"></label>
      <!-- <label>Poplatok <input type="number" step="0.000001" name="fee" value="0"></label> -->
      <label>Mena
        <select name="ccy">
          <option>EUR</option><option>USD</option><option>GBP</option><option>CZK</option>
        </select>
      </label>
      <button type="submit">Pridať</button>
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
    <h2 style="margin-top:0">Transakcie</h2>
    <table id="transactionsTable">
      <thead>
        <tr><th>Dátum</th><th>Provider</th><th>Typ</th><th>Ticker</th><th>Qty</th><th>Cena</th><th>Mena</th><th class="right">—</th><th></th><th></th></tr>
      </thead>
      <tbody></tbody>
    </table>
  </section> 
</body>
</html>

<!-- Note modal -->
<div id="noteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:9999;">
  <div style="background:#fff;border-radius:6px;max-width:600px;width:90%;box-shadow:0 10px 30px rgba(0,0,0,0.2);box-sizing: border-box; padding:10px;">
    <h3 style="margin-top:0">Pridať poznámku</h3>
  <!-- <div id="noteExistingList" style="max-height:160px;overflow:auto;margin-bottom:8px;border:1px solid #f0f0f0;padding:8px;border-radius:6px;background:#fafafa;font-size:13px;color:#222"></div> -->
  <textarea id="noteText" placeholder="Napíš novú poznámku sem..." style="width:100%;height:120px;margin-bottom:8px;padding:8px;font-size:14px;box-sizing:border-box"></textarea>
    <div style="text-align:right">
      <button id="noteCancel" class="secondary" style="margin-right:8px">Zrušiť</button>
      <button id="noteSave">Uložiť</button>
    </div>
  </div>
</div>

<!-- Notes list modal -->
<div id="notesListModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:9999;">
  <div style="background:#fff;padding:16px;border-radius:6px;max-width:800px;width:95%;max-height:80vh;overflow:auto;box-shadow:0 10px 30px rgba(0,0,0,0.2);">
    <h3 style="margin-top:0">Zoznam poznámok</h3>
    <div id="notesListContent" style="margin-bottom:12px;font-size:14px;line-height:1.4;"></div>
    <div style="text-align:right">
      <button id="notesListClose" class="secondary">Zatvoriť</button>
    </div>
  </div>
</div>
</html>