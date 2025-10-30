
const txForm = document.getElementById('txForm'); // Transaction form
const date  = document.querySelector('input[type="date"]'); // Date input field
const provider = document.querySelector('select[name="provider"]'); // Broker/provider select field
const ticker = document.querySelector('input[name="symbol"]'); // Ticker symbol input field
const type = document.querySelector('select[name="type"]'); // Transaction type select field
const quantity = document.querySelector('input[name="qty"]'); // Quantity input field
const price = document.querySelector('input[name="price"]'); // Price input field
const ccy = document.querySelector('select[name="ccy"]'); // Currency select field
const transactionsTable = document.getElementById("transactionsTable"); // Transactions table container
const notesListClose = document.getElementById("notesListClose"); // Notes list modal close button


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
    } 
    
    if (e.target && e.target.matches("button[data-note]")) {
        const transactionId = e.target.getAttribute("data-note");
        showNoteModal(transactionId);
    }

    if (e.target && e.target.matches("span.note-count")) {
        const transactionId = e.target.getAttribute("data-id");
        notesListModal(transactionId); // TOTO
    }
});

let saveTimeout;
let isSaving = false;

transactionsTable.addEventListener('focusout', function(e) {
    if (e.target.matches('td[contenteditable="true"]')) {
        clearTimeout(saveTimeout);
        
        saveTimeout = setTimeout(() => {
            if (isSaving) {
                console.log('Už beží request, preskakujem...');
                return;
            }
            
            isSaving = true;
            
            const id = e.target.getAttribute('data-id');
            const field = e.target.getAttribute('data-field');
            const value = e.target.textContent.trim();
            
            console.log(`Ukladám: ID=${id}, field=${field}, value=${value}`);
            
            const xhttp = new XMLHttpRequest();
            
            xhttp.onreadystatechange = function() {
                if (this.readyState === 4) {
                    isSaving = false; // Uvoľni flag
                    
                    if (this.status === 200) {
                        console.log('✓ Uložené');
                    } else {
                        console.error('✗ Chyba pri ukladaní');
                    }
                }
            };
            
            xhttp.open("POST", "portfolio_update.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(`id=${id}&field=${field}&value=${value}`);
            
        }, 500); // Počká 500ms po poslednom focusout
    }
});


 noteSaveBtn.addEventListener('click', () => {
 });


 noteCancelBtn.addEventListener('click', () => {
        // clear textarea and close
        if (el('#noteText')) el('#noteText').value = '';
        if (noteModal) {
          delete noteModal.dataset.currentId;
          noteModal.style.display = 'none';
        }
      });
 noteModal.addEventListener('click', (e) => {
        if (e.target === noteModal) {
          if (el('#noteText')) el('#noteText').value = '';
          delete noteModal.dataset.currentId;
          noteModal.style.display = 'none';
        }
      });



notesListClose.addEventListener('click', function(e) {
    document.getElementById("notesListModal").style.display = 'none';
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

/* function notesListModal(transactionId) {
    const notesListModal = document.getElementById("notesListModal");
    console.log('Opening notes for transaction ID: ' + transactionId);
    const notesListContent = document.getElementById("notesListContent");
    notesListModal.style.display = 'flex';
    console.log('Opening notes for transaction ID: ' + transactionId);
    notesListContent.innerHTML = '<p>Loading notes...</p>';
    
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4) {
            if (this.status === 200) {
                notesListContent.innerHTML = this.responseText;
            } else {
                notesListContent.innerHTML = '<p>Error loading notes.</p>';
            }
        }
    };
    xhttp.open("POST", "notes.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`id=${transactionId}`);
} */

function notesListModal(transactionId) {
    const notesListModal = document.getElementById('notesListModal'); // BEZ #
    const notesListContent = document.getElementById('notesListContent');
    
    if (!notesListModal || !notesListContent) return;
    
    notesListModal.style.display = 'flex';
    notesListContent.innerHTML = '<p>Loading notes...</p>';

    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4) {
            if (this.status === 200) {
                notesListContent.innerHTML = this.responseText;
            } else {
                notesListContent.innerHTML = '<p>Error loading notes.</p>';
            }
        }
    };
    xhttp.open("POST", "notes.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`id=${transactionId}`);
}

async function updateTransaction(id, field, value) {
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            alert('Transaction updated successfully!');
            loadPortfolio(); // Refresh portfolio data
        }
    };
    xhttp.open("POST", "portfolio_update.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`id=${id}&field=${field}&value=${value}`);
}