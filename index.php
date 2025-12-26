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
      <div class="muted">Hodnota portfólia</div>
      <div id="portfolioValue" class="stat-value"><?php echo  GetPortfolioValue(); ?></div>
      <div id="portfolioPnL" class="muted">P/L: —</div>
    </div>
    <div class="card">
      <div class="muted">Počet pozícií</div>
      <div id="positionsCount" class="stat-value"><?php echo GetCountPosiotions(); ?></div>
      <div id="cashInfo" class="muted">Hotovosť: —</div>
    </div>
  </section>

  <setion class="grid">
    <div class="card">
      <h1>Kategoria assetu</h1>
      <canvas id="myChart"></canvas>
      <script>
        let chartAssetCategoryInstance = null;

        // Stabilné HEX farby podľa value (trvalé)
        const assetCategoryColorMap = {
          STOCKS: '#36A2EB',
          ETF: '#4BC0C0',
          FUND: '#9966FF',
          BOND: '#FFCE56',
          CRYPTO: '#FF6384',
        };

        const getAssetCategoryColor = (category) => {
          const key = String(category || '').toUpperCase().trim();
          return assetCategoryColorMap[key] ?? '#C9CBCF'; // fallback
        };

        // HEX s alpha kanálom: #RRGGBBAA (B3 ~ 70% opacity)
        const withAlpha = (hex, alphaHex = 'B3') => `${hex}${alphaHex}`;

        async function loadAndRenderAssetCategoryChart() {
          const canvas = document.getElementById('myChart');
          const errorEl = document.getElementById('myChartError'); // voliteľné

          try {
            if (!canvas) {
              throw new Error('Canvas element #myChart neexistuje.');
            }
            if (errorEl) errorEl.textContent = '';

            const response = await fetch('get_asset_categories.php', {
              headers: { 'Accept': 'application/json' },
            });

            if (!response.ok) {
              throw new Error(`Problém pri načítaní dát zo servera: ${response.status} ${response.statusText}`);
            }

            const apiData = await response.json();

            // očakávame: { labels: ['STOCKS','ETF',...], data: [10,20,...] }
            if (!apiData?.labels || !apiData?.data) {
              throw new Error('Neočakávaný formát JSON. Očakávam {labels: [], data: []}.');
            }

            const baseColors = apiData.labels.map(getAssetCategoryColor);

            if (chartAssetCategoryInstance) {
              chartAssetCategoryInstance.destroy();
            }

            chartAssetCategoryInstance = new Chart(canvas, {
              type: 'bar',
              data: {
                labels: apiData.labels,
                datasets: [{
                  label: '# of asset per category',
                  data: apiData.data,
                  backgroundColor: baseColors.map(c => withAlpha(c, 'B3')),
                  borderColor: baseColors,
                  borderWidth: 1
                }]
              },
              options: {
                plugins: {
                  legend: {
                    labels: { color: '#FFFFFF' }
                  },
                  tooltip: {
                    titleColor: '#FFFFFF',
                    bodyColor: '#FFFFFF'
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    ticks: { color: '#FFFFFF' }
                  },
                  x: {
                    ticks: { color: '#FFFFFF' }
                  }
                }
              }
            });

          } catch (error) {
            console.error('Chyba pri vykresľovaní grafu (asset category):', error);
            if (errorEl) {
              errorEl.textContent = 'Nepodarilo sa načítať dáta grafu.';
            }
          }
        }

        document.addEventListener('DOMContentLoaded', () => {
          loadAndRenderAssetCategoryChart();
        });
      </script>
    </div>
    
    <div class="card">
      <h1>Asset:</h1>
      <canvas id="myChart2"></canvas>
      <script>
    // Asynchrónna funkcia, ktorá načíta dáta z PHP a vykreslí graf
          async function loadAndRenderChart() {
              try {
                  // Zavolá PHP skript, ktorý sme vytvorili vyššie
                  const response = await fetch('get_asset.php');
                  
                  if (!response.ok) {
                      throw new Error('Problém pri načítaní dát zo servera: ' + response.statusText);
                  }

                  // Premení odpoveď servera (JSON text) na JavaScript objekt
                  const apiData = await response.json();

                  const ctx2 = document.getElementById('myChart2');

                  // Vytvorí novú inštanciu grafu Chart.js
                  new Chart(ctx2, {
                      type: 'bar',
                      data: {
                          // POUŽIJEME DÁTA Z PHP ODPOVEDE
                          labels: apiData.labels, 
                          datasets: [{
                              label: 'Počet transakcií',
                              // POUŽIJEME DÁTA Z PHP ODPOVEDE
                              data: apiData.data, 
                              borderWidth: 1,
                              backgroundColor: [
                                  'rgba(255, 99, 132, 0.6)',
                                  'rgba(54, 162, 235, 0.6)',
                                  'rgba(255, 206, 86, 0.6)',
                                  'rgba(75, 192, 192, 0.6)',
                                  'rgba(153, 102, 255, 0.6)',
                                  'rgba(255, 159, 64, 0.6)'
                              ],
                              borderColor: [
                                  'rgba(255, 99, 132, 1)',
                                  'rgba(54, 162, 235, 1)',
                                  'rgba(255, 206, 86, 1)',
                                  'rgba(75, 192, 192, 1)',
                                  'rgba(153, 102, 255, 1)',
                                  'rgba(255, 159, 64, 1)'
                              ]
                          }]
                      },
                      options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#FFFFFF' // Farba textu na Y osi (čísla)
                            },
                            grid: {
                                display: false // Skryje mriežku na Y osi
                            }
                        },
                        x: {
                            ticks: {
                                color: '#FFFFFF' // Farba textu na X osi (názvy assetov)
                            },
                            grid: {
                                display: false // Skryje mriežku na X osi
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#FFFFFF' // Farba textu v legende
                            }
                        }
                    }
                  } 
                  });

              } catch (error) {
                  console.error('Chyba pri vykresľovaní grafu:', error);
                  // Zobrazí chybovú správu používateľovi, ak sa dáta nenačítajú
                  document.getElementById('myChart2').innerText = 'Nepodarilo sa načítať dáta grafu.';
              }
          }

          // !! DÔLEŽITÉ: Túto funkciu musíme ZAVOLAŤ, aby sa kód spustil
          loadAndRenderChart();
      </script>
    </div>   

    <div class="card">
      <h1>Mena:</h1>
      <canvas id="myChart3"></canvas>
       <script>
        let chart3Instance = null;

        // Pevné farby pre známe meny (HEX)
        const currencyColorMap = {
          EUR: '#36A2EB',
          USD: '#FF6384',
          GBP: '#4BC0C0',
          CZK: '#FFCE56',
          PLN: '#9966FF',
        };

        // Fallback paleta (HEX)
        const fallbackPalette = [
          '#FF9F40',
          '#C9CBCF',
          '#63FF84',
          '#8463FF',
          '#FF63FF',
          '#63FFFF',
          '#FFD86B',
          '#5AD1B9',
        ];

        const hashString = (str) => {
          let hash = 0;
          for (let i = 0; i < str.length; i += 1) {
            hash = ((hash << 5) - hash) + str.charCodeAt(i);
            hash |= 0;
          }
          return Math.abs(hash);
        };

        const getCurrencyColor = (currency) => {
          const code = String(currency || '').toUpperCase().trim();
          if (currencyColorMap[code]) return currencyColorMap[code];
          return fallbackPalette[hashString(code) % fallbackPalette.length];
        };

        // Ak chceš “priehľadnosť” s HEX: pridaj AA (00..FF)
        // napr. 70% ≈ B3 => #RRGGBBB3
        const withAlpha = (hex, alphaHex = 'B3') => `${hex}${alphaHex}`;

        async function loadAndRenderCurrencies() {
          const canvas = document.getElementById('myChart3');
          const errorBox = document.getElementById('myChart3Error');

          try {
            if (!canvas) throw new Error('Canvas element #myChart3 neexistuje.');
            if (errorBox) errorBox.textContent = '';

            const response = await fetch('get_currencies.php', {
              headers: { 'Accept': 'application/json' },
            });

            if (!response.ok) {
              throw new Error(`Problém pri načítaní dát zo servera: ${response.status} ${response.statusText}`);
            }

            const apiDataCCy = await response.json();
            if (!apiDataCCy?.labels || !apiDataCCy?.data) {
              throw new Error('Neočakávaný formát JSON. Očakávam {labels: [], data: []}.');
            }

            const baseColors = apiDataCCy.labels.map(getCurrencyColor);

            if (chart3Instance) chart3Instance.destroy();

            chart3Instance = new Chart(canvas, {
              type: 'bar',
              data: {
                labels: apiDataCCy.labels,
                datasets: [{
                  label: '# of transactions by currency',
                  data: apiDataCCy.data,
                  backgroundColor: baseColors.map(c => withAlpha(c, 'B3')), // ~70% opacity
                  borderColor: baseColors, // plná farba
                  borderWidth: 1
                }]
              },
              options: {
                plugins: {
                  legend: {
                    labels: {
                      color: '#FFFFFF' // <- toto spraví text legendy biely
                    }
                  },
                  tooltip: {
                    titleColor: '#FFFFFF',
                    bodyColor: '#FFFFFF'
                  }
                },
                scales: {
                  y: { beginAtZero: true, ticks: { color: '#FFFFFF' } },
                  x: { ticks: { color: '#FFFFFF' } }
                }
              }
            });

          } catch (error) {
            console.error('Chyba pri vykresľovaní grafu:', error);
            if (errorBox) errorBox.textContent = 'Nepodarilo sa načítať dáta grafu.';
          }
        }

        document.addEventListener('DOMContentLoaded', () => {
          loadAndRenderCurrencies();
        });
      </script>
    </div>   

  </setion>
          


  <section class="card">
    <h2 class="section-heading">Nová transakcia</h2>
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
      <label>Ticker <input name="symbol" placeholder="AAPL, MSFT..." autocomplete="off"/></label>
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

  <section class="card">
    <h2 class="section-heading">Filter</h2>
    <div class="transactionsFilter_wrap">
      <div class= "transactionsFilter">
        <button name="StocksFilter" class="secondary">Akcie</button><button name="FundsFilter" class="secondary">Fondy</button></button><button name="BondsFilter" class="secondary">Dlhopisy</button><button name="cryptoFilter" class="secondary">Crypto</button><button name="AllFilter" class="secondary">Všetko</button>
      </div>
      <div class= "transactionsFilter">
        <?php 

            $get_tickers = "SELECT DISTINCT symbol FROM transactions";
            $result = mysqli_query($link, $get_tickers) or die("MySQL ERROR: " . mysqli_error($link));
            while ($row = mysqli_fetch_array($result)) {
              $symbol = $row['symbol'];
              echo '<button name="symbolFilter" class="secondary">'.$symbol.'</button>';
            }
        ?>
      </div>
    </div><!-- transactionsFilter_wrap -->        
  </section> 

  <section class="card">
    <h2 class="section-heading">Transakcie</h2>
    <table id="transactionsTable">
      <thead>
        <tr>
          <th>Ticker</th><th>Typ</th><th>Kategória</th><th>Množstvo</th><th>Cena</th><th>Poplatok</th><th>TP</th><th>SL</th><th>Mena</th><th>Dátum</th>
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
               
                
                $tp = $row['tp'];
                $sl = $row['sl'];

                if($tp =="" || $sl=="") {
                  $tp = "-----";
                  $sl = "-----";
                } else {
                  $tp = $row['tp'];
                  $sl = $row['sl'];
                }
               
                
                $created_at = $row['created_at'];
                echo "<tr><td>$symbol</td><td>$type</td><td>$category</td><td class='editable-cell' contenteditable='true' data-id='$idx'>$qty</td><td contenteditable='true' class='editable-cell'data-id='$idx'>$price</td><td>$fee</td><td><div class='take-profit'>$tp</div></td><td><div class='stop-loss'>$sl</div></td><td>$ccy</td><td>$created_at</td><td><span class='note-count' data-id='$idx' title='Poznámky'>".GetCountNotes($idx)."</span></td><td><div class='actions-container'><button data-note='$idx' data-id='$idx' class='secondary'><i class='far fa-plus-square'></i>Pridať poznámku</button><button data-modify='$idx' data-id='$idx' class='secondary'><i class='far fa-edit'></i>Upraviť</button><button data-delnote='$idx' data-id='$idx' class='secondary'><i class='far fa-trash-alt'></i>Zmazať</button></div></td></tr>";
            }
        ?>
     
      </tbody>
    </table>
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