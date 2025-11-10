
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

// Utility function for fetch with timeout
function fetchWithTimeout(url, options = {}, timeout = 10000) {
    return Promise.race([
        fetch(url, options),
        new Promise((_, reject) => 
            setTimeout(() => reject(new Error('Request timeout')), timeout)
        )
    ]);
}

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
        
        saveTimeout = setTimeout(async () => {
            if (isSaving) {
                console.log('Už beží request, preskakujem...');
                return;
            }
            
            isSaving = true;
            
            const id = e.target.getAttribute('data-id');
            const field = e.target.getAttribute('data-field');
            const value = e.target.textContent.trim();
            
            console.log(`Ukladám: ID=${id}, field=${field}, value=${value}`);
            
            try {
                const response = await fetchWithTimeout('portfolio_update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&field=${field}&value=${value}`
                }, 10000);
                
                if (response.ok) {
                    console.log('✓ Uložené');
                } else {
                    console.error('✗ Chyba pri ukladaní:', response.status);
                }
            } catch (error) {
                console.error('✗ Chyba pri ukladaní:', error);
            } finally {
                isSaving = false;
            }
        }, 500);
    }
});


noteSaveBtn.addEventListener('click', async () => {
      const noteModal = document.getElementById("noteModal");
      const noteText = document.getElementById("noteText");
      const transactionId = noteModal.dataset.currentId;
      const noteContent = noteText.value.trim();
      
      if (noteContent === '') {
          alert('Note content cannot be empty');
          return;
      }
      
      try {
          const response = await fetchWithTimeout('note_create.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: `transactionId=${transactionId}&noteContent=${encodeURIComponent(noteContent)}`
          }, 10000);
          
          if (response.ok) {
              console.log('Note saved successfully');
              noteText.value = '';
              noteModal.style.display = 'none';
          } else {
              throw new Error(`HTTP ${response.status}`);
          }
      } catch (error) {
          console.error('Error saving note:', error);
          alert('Failed to save note: ' + error.message);
      }
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
    try {
        const params = new URLSearchParams({
            date, provider, ticker, type, category, quantity, price, ccy
        });
        
        const response = await fetchWithTimeout('portfolio_add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        }, 10000);
        
        if (response.ok) {
            alert('Transaction saved successfully!');
            txForm.reset();
            // Refresh portfolio only once
            await Promise.all([loadPortfolio(), countPortfolio()]);
        } else {
            throw new Error(`HTTP ${response.status}`);
        }
    } catch (error) {
        console.error('Error saving transaction:', error);
        alert('Failed to save transaction: ' + error.message);
    }
}


async function loadPortfolio() {
  const box = document.getElementById("transactionsTable");
  if (!box) return;
  
  try {
    box.innerHTML = '<div class="loading">Načítavam portfólio...</div>';
    
    const response = await fetchWithTimeout('portfolio.php', {
      method: 'GET'
    }, 15000);
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    
    const text = await response.text();
    const data = JSON.parse(text.trim());
    
    if (data.success && data.transactions) {
      box.innerHTML = renderTransactionsTable(data.transactions);
    } else {
      box.textContent = "Chyba: " + (data.error || "Neznáma chyba");
    }
  } catch (error) {
    console.error("Error loading portfolio:", error);
    box.textContent = `Nepodarilo sa načítať portfólio: ${error.message}`;
  }
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
    try {
        const response = await fetchWithTimeout('portfolio_delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${transactionId}`
        }, 10000);
        
        if (response.ok) {
            alert('Transaction removed successfully!');
            await Promise.all([loadPortfolio(), countPortfolio()]);
        } else {
            throw new Error(`HTTP ${response.status}`);
        }
    } catch (error) {
        console.error('Error removing transaction:', error);
        alert('Failed to remove transaction: ' + error.message);
    }
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

async function notesListModal(transactionId) {
    const notesListModal = document.getElementById('notesListModal');
    const notesListContent = document.getElementById('notesListContent');
    
    if (!notesListModal || !notesListContent) return;
    
    notesListModal.style.display = 'flex';
    notesListContent.innerHTML = '<p>Loading notes...</p>';

    try {
        const response = await fetchWithTimeout('notes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${transactionId}`
        }, 10000);
        
        if (response.ok) {
            notesListContent.innerHTML = await response.text();
        } else {
            notesListContent.innerHTML = '<p>Error loading notes.</p>';
        }
    } catch (error) {
        console.error('Error loading notes:', error);
        notesListContent.innerHTML = '<p>Error loading notes: ' + error.message + '</p>';
    }
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
    try {
        const response = await fetchWithTimeout('portfolio_filter.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `filter=${filterName}`
        }, 10000);
        
        if (response.ok) {
            const portfolioTable = document.getElementById("portfolioTable");
            if (portfolioTable) {
                portfolioTable.innerHTML = await response.text();
            }
        } else {
            console.error('Filter failed:', response.status);
        }
    } catch (error) {
        console.error('Error filtering transactions:', error);
    }
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
    try {
        const response = await fetchWithTimeout('portfolio_count.php', {
            method: 'GET'
        }, 10000);
        
        if (response.ok) {
            const text = await response.text();
            const countEl = document.getElementById("positionsCount");
            if (countEl) countEl.innerHTML = text;
        }
    } catch (error) {
        console.error('Error counting portfolio:', error);
    }
}
