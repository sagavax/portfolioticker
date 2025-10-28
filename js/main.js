
const txForm = document.getElementById('txForm'); // Transaction form
const date  = document.querySelector('input[type="date"]'); // Date input field
const provider = document.querySelector('select[name="provider"]'); // Broker/provider select field
const ticker = document.querySelector('input[name="symbol"]'); // Ticker symbol input field
const type = document.querySelector('select[name="type"]'); // Transaction type select field
const quantity = document.querySelector('input[name="qty"]'); // Quantity input field
const price = document.querySelector('input[name="price"]'); // Price input field
const ccy = document.querySelector('select[name="ccy"]'); // Currency select field
const transactionsTable = document.getElementById("transactionsTable");


document.addEventListener("DOMContentLoaded", loadPortfolio);


txForm.addEventListener('submit', function(e) {
    e.preventDefault();
    // Further processing can be done here
    if(date.value && provider.value && ticker.value && type.value && quantity.value && price.value && ccy.value) {
      if(quantity.value <= 0 || price.value <= 0) {
          console.warn('Quantity and Price must be greater than zero');
          return;
      }

      if(ticker.value ==="") {
          alert('Ticker symbol cannot be empty');
          return;
      }
       
    } else {
        console.log(date.value, provider.value, ticker.value, type.value, quantity.value, price.value, ccy.value); 
        saveTransaction(date.value, provider.value, ticker.value, type.value, quantity.value, price.value, ccy.value);
    } 
});

transactionsTable.addEventListener('click', function(e) {
    if (e.target && e.target.matches("button[data-del]")) {
        const transactionId = e.target.getAttribute("data-del");
        deleteTransaction(transactionId);
    } if (e.target && e.target.matches("button[data-note]")) {
        const transactionId = e.target.getAttribute("data-note");
        showNoteModal(transactionId);
    }

    if( e.target && e.target.matches("span.note-count")) {
        const transactionId = e.target.getAttribute("data-id");
         document.getElementById("notesListModal").style.display = 'flex';
        //notesListModal(transactionId);
    }
});




async function saveTransaction(date, provider, ticker, type, quantity, price, ccy) {
    //console.log('Saving transaction:', date, provider, ticker, type, quantity, price, ccy);
    // Here you would typically send the data to the server using fetch or XMLHttpRequest
    const xhttp = new XMLHttpRequest();
    
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            alert('Transaction saved successfully!');
            // Optionally, reset the form or update the UI
            //loadPortfolio(); // Refresh portfolio data
            txForm.reset();
        }
    };
    //xhttp.open("POST", "api/transactions/transaction_add.php", true);
    xhttp.open("POST", "portfolio_add.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`date=${date}&provider=${provider}&ticker=${ticker}&type=${type}&quantity=${quantity}&price=${price}&ccy=${ccy}`);
}


function loadPortfolio() {
  const xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState !== 4) return;
    const box = document.getElementById("transactionsTable");
    if (!box) return;
    
    if (this.status === 200) {
      try {
        const data = JSON.parse(this.responseText.trim());
        
        // OPRAVA: posielaj data.transactions, nie celý data objekt
        if (data.success && data.transactions) {
          box.innerHTML = renderTransactionsTable(data.transactions);
        } else {
          box.textContent = "Chyba: " + (data.error || "Neznáma chyba");
        }
      } catch (e) {
        console.error("JSON parse error:", e);
        console.warn("RAW 200 response:", this.responseText);
        box.textContent = "Chybný JSON.";
      }
    } else {
      console.error("HTTP", this.status);
      console.error("RAW error response:", this.responseText);
      box.textContent = "Nepodarilo sa načítať portfólio (HTTP " + this.status + ").";
    }
  };
  xhttp.open("GET", "portfolio.php", true);
  xhttp.send();
}

function escapeHtml(s = "") {
  return String(s)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

function renderTransactionsTable(rows = []) {
  if (!Array.isArray(rows) || rows.length === 0) {
    return '<div class="empty">Žiadne transakcie</div>';
  }
  
  const head = `
    <thead>
      <tr>
        <th>Dátum</th><th>Broker</th><th>Ticker</th><th>Typ</th>
        <th>Množstvo</th><th>Cena</th><th>Mena</th>
      </tr>
    </thead>`;
  
  const body = rows.map(r => {
    // Formátovanie čísel - odstráni nadbytočné nuly
    const qty = r.qty ? parseFloat(r.qty) : 0;
    const price = r.price ? parseFloat(r.price).toFixed(2) : '0.00';
    
    return `
    <tr>
      <td>${escapeHtml(r.date)}</td>
      <td>${escapeHtml(r.provider)}</td>
      <td><strong>${escapeHtml(r.symbol || '-')}</strong></td>
      <td>${escapeHtml(r.type)}</td>
      <td data-id="${r.id}" data-field="qty" contenteditable="true" class="text-right">${qty}</td>
      <td data-id="${r.id}" data-field="price" contenteditable="true" class="text-right">${price}</td>
      <td>${escapeHtml(r.ccy)}</td>
      <td class="right">
        <span class="note-count" data-id="${r.id}" title="Poznámky">0</span>
        <button class="secondary" data-note="${r.id}">Poznamka +</button>
        <button class="secondary" data-del="${r.id}">Zmaž</button>
      </td>
    </tr>`
  }).join("");
  
  return `<table class="table table-striped">${head}<tbody>${body}</tbody></table>`;
}


async function removeFromPortfolio(transactionId) {
    const xhttp = new XMLHttpRequest();
    
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            alert('Transaction removed successfully!');
            loadPortfolio(); // Refresh portfolio data
        }
    };
    
    xhttp.open("POST", "portfolio_delete.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`id=${transactionId}`);
  
}

async function notesListModal(transactionId) {
    document.getElementById("notesListModal").style.display = 'flex';
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("notesListModal").innerHTML = this.responseText;
        }
    };
    xhttp.open("POST", "notes.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`id=${transactionId}`);
}