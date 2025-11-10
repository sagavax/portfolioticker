
const txForm = document.getElementById('txForm'); // Transaction form
const date  = document.querySelector('input[type="date"]'); // Date input field
const provider = document.querySelector('select[name="provider"]'); // Broker/provider select field
const ticker = document.querySelector('input[name="symbol"]'); // Ticker symbol input field
const type = document.querySelector('select[name="type"]'); // Transaction type select field
const category = document.querySelector('select[name="category"]'); // Transaction category select field
const quantity = document.querySelector('input[name="qty"]'); // Quantity input field
const price = document.querySelector('input[name="price"]'); // Price input field
const ccy = document.querySelector('select[name="ccy"]'); // Currency select field
const transactionsTable = document.getElementById("transactionsTable"); // Transactions table container
const notesListClose = document.getElementById("notesListClose"); // Notes list modal close button
const noteSaveBtn = document.querySelector('#noteSave');
const noteCancelBtn = document.querySelector('#noteCancel');
const transactionsFilter = document.querySelector(".transactionsFilter"); // Transactions filter container


//
document.addEventListener("DOMContentLoaded", loadPortfolio);


txForm.addEventListener('submit', function(e) {
    e.preventDefault();
    // Further processing can be done here
    if(date.value && provider.value && type.value && category.value && quantity.value && price.value && ccy.value) {
      if(quantity.value <= 0 || price.value <= 0) {
          console.warn('Quantity and Price must be greater than zero');
          return;
      }

      if(ticker.value ==="") {
          alert('Ticker symbol cannot be empty');
          return;
      }
       
        console.log(date.value, provider.value, type.value, category.value, ticker.value, quantity.value, price.value, ccy.value); 
        saveTransaction(date.value, provider.value, ticker.value, type.value,category.value, quantity.value, price.value, ccy.value);
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
      xhttp = new XMLHttpRequest();
      const noteModal = document.getElementById("noteModal");
      const noteText = document.getElementById("noteText");
      const transactionId = noteModal.dataset.currentId;
      const noteContent = noteText.value.trim();
      if (noteContent === '') {
          alert('Note content cannot be empty');
          return;
      }
      xhttp.onreadystatechange = function() {
          if (this.readyState === 4) {
              if (this.status === 200) {
                  console.log('Note saved successfully');
                  // Optionally, refresh notes list or update UI
                  noteText.value = '';
              } else {
                  console.error('Error saving note');
              }
          }
      }
      xhttp.open("POST", "note_create.php", true);
      xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhttp.send(`transactionId=${transactionId}&noteContent=${noteContent}`);
      noteModal.style.display = 'none';
 });


 noteCancelBtn.addEventListener('click', () => {
        // clear textarea and close
        if (document.querySelector('#noteText')) document.querySelector('#noteText').value = '';
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

transactionsFilter.addEventListener('click', function(e) {
    if (e.target && e.target.matches("button[name$='Filter']")) {
        const filterName = e.target.getAttribute("name");
        filterTransactions(filterName);
    }
});

async function saveTransaction(date, provider, ticker, type, category, quantity, price, ccy) {
    //console.log('Saving transaction:', date, provider, ticker, type, quantity, price, ccy);
    // Here you would typically send the data to the server using fetch or XMLHttpRequest
    const xhttp = new XMLHttpRequest();
    
    xhttp.onreadystatechange = async function() {
        if (this.readyState === 4 && this.status === 200) {
            alert('Transaction saved successfully!');
            await countPortfolio(); // Refresh portfolio data
            // Optionally, reset the form or update the UI
            await loadPortfolio(); // Refresh portfolio data
            txForm.reset();
        }
    };
    //xhttp.open("POST", "api/transactions/transaction_add.php", true);
    xhttp.open("POST", "portfolio_add.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`date=${date}&provider=${provider}&ticker=${ticker}&type=${type}&category=${category}&quantity=${quantity}&price=${price}&ccy=${ccy}`);
    /*
    
date 2025-10-31
provider Robinhood
ticker
type BUY
category STOCK,
quantity 1
price 20
ccy EUR

*/

}


async function loadPortfolio() {
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
        <th>Kategória</th><th>Množstvo</th><th>Cena</th><th>Mena</th><th>bla</th><th>bla</th>
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
      <td>${escapeHtml(r.category)}</td>
      <td data-id="${r.id}" data-field="qty" contenteditable="true" class="text-right">${qty}</td>
      <td data-id="${r.id}" data-field="price" contenteditable="true" class="text-right">${price}</td>
      <td>${escapeHtml(r.ccy)}</td>
      <td></td>
      <td></td>
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
    
    xhttp.onreadystatechange = async function() {
        if (this.readyState === 4 && this.status === 200) {
            alert('Transaction removed successfully!');
            await loadPortfolio(); // Refresh portfolio data
            await countPortfolio();
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

function showNoteModal(transactionId) {
    const noteModal = document.getElementById("noteModal");
    const noteText = document.getElementById("noteText");
    noteModal.dataset.currentId = transactionId;
    noteModal.style.display = 'flex';
    noteText.focus();
}


async function deleteTransaction(transactionId) {
    if (confirm('Are you sure you want to delete this transaction?')) {
        await removeFromPortfolio(transactionId);
    }
}


async function filterTransactions(filterName) {
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("portfolioTable").innerHTML = this.responseText;
        }
    };
    xhttp.open("POST", "portfolio_filter.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(`filter=${filterName}`);
}

/* async function removeFromPortfolio(transactionId) {
    // Implementation here
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
} */


    async function countPortfolio() {
        // Implementation here
        const xhttp = new XMLHttpRequest();
        
        xhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                //alert('Transaction removed successfully!');
                document.getElementById("positionsCount").innerHTML = this.responseText;
            }
        };
        
        xhttp.open("GET", "portfolio_count.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send();
        };
