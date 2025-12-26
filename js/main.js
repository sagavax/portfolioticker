
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
const assetListModal = document.getElementById("assetListModal");
const ssetListSearch = document.getElementById("assetListSearch");
const assetListClose = document.getElementById("assetListClose");
const modalModifyPosition = document.getElementById("modalModifyPosition");
const modalStopLossTakeProfit = document.getElementById("modalStopLossTakeProfit");
const modalModifyFees = document.getElementById("modalModifyFees");

setInterval(updateClock, 1000);

updateClock();


modalModifyFeesClose.addEventListener('click', function(e) {
    modalModifyFees.style.display = 'none';
});

modalStopLossTakeProfitClose.addEventListener('click', function(e) {
    modalStopLossTakeProfit.style.display = 'none';
})

assetListClose.addEventListener('click', function(e) {
    assetListModal.style.display = 'none';
});

assetListModal.addEventListener('click', function(e) {
    if(e.target && e.target.tagName ==="BUTTON") {
        if(e.target.name ==="symbol") {
            txForm.querySelector('input[name="symbol"]').value = e.target.textContent;
            assetListSearch.value="";
            assetListModal.style.display = 'none';
            e.target.remove();
        }
    } 
});

assetListModal.addEventListener('input', function(e) {
    if(e.target && e.target.tagName ==="INPUT") {
        searchAsset(e.target.value);
    } 
});

// Utility function for fetch with timeout

//
//document.addEventListener("DOMContentLoaded", loadPortfolio);


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

txForm.addEventListener('click', function(e) {
    if(e.target && e.target.tagName ==="INPUT") {
        const assetListModal = document.getElementById("assetListModal");
        assetListModal.style.display = 'flex';
        /* const transactionId = e.target.getAttribute("data-del");
        deleteTransaction(transactionId); */
    }
})

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

    if(e.target && e.target.matches("button[data-modify]")) {
        const transactionId = e.target.getAttribute("data-modify");
        modifyPosition(transactionId);
    }

    if(e.target.classList.contains("stop-loss") || e.target.classList.contains("take-profit")) {
        const transactionId = e.target.getAttribute("data-id");
        console.log(e.target);
           modifyTakeProfitStopLoss(transactionId);
           //document.getElementById("modalStopLossTakeProfit").style.display = 'flex';
    }

    if(e.target.classList.contains("modify-fee")) {
        const transactionId = e.target.getAttribute("data-id");
        console.log(e.target);
           modifyFees(transactionId);
           //document.getElementById("modalModifyFees").style.display = 'flex';
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


function showAssetModal(){
    const assetModal = document.getElementById("assetListModal");
    assetModal.style.display = 'flex';
}

async function deleteTransaction(transactionId) {
    if (confirm('Are you sure you want to delete this transaction?')) {
        await removeFromPortfolio(transactionId);
    }
}


async function filterTransactions(filterName) {
    const box = document.getElementById("transactionsTable");
    if (!box) return;
    
    try {
        box.innerHTML = '<div class="loading">Filtrujem...</div>';
        
        const response = await fetchWithTimeout('portfolio_filter.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `filter=${filterName}`
        }, 10000);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.transactions) {
            box.innerHTML = renderTransactionsTable(data.transactions);
        } else {
            box.textContent = "Chyba: " + (data.error || "Neznáma chyba");
        }
    } catch (error) {
        console.error('Error filtering transactions:', error);
        box.textContent = `Chyba pri filtrovaní: ${error.message}`;
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

function updateClock() {
    // 1. Get the current time
    const now = new Date();
    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();

    // 2. Determine if it's AM or PM
    const period = hours >= 12 ? 'PM' : 'AM';

    // 3. Convert from 24-hour to 12-hour format
    // If hours is 0 (midnight), it should be 12
    if (hours === 0) {
        hours = 12;
    }
    // If hours is greater than 12, subtract 12
    if (hours > 12) {
        hours = hours - 12;
    }

    // 4. Add a leading zero to single-digit minutes and seconds
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;
    
    // 5. Construct the final time string
    const timeString = `${hours}:${minutes}:${seconds}`;

    // 6. Update the HTML elements with the new time
    document.getElementById('clock').textContent = timeString;
    //document.getElementById('clock-period').textContent = period;
}

// Run the function every second (1000 milliseconds)
//

// Run the function once immediately on page load
//;

async function GetAllTransactionaAssets(){
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            json = JSON.parse(this.responseText);
            console.log(json);
        }
    }
    xhttp.open("GET", "get_assets.php", true);
    xhttp.send();     
}

function fetchWithTimeout(url, options = {}, timeout = 10000) {
    return Promise.race([
        fetch(url, options),
        new Promise((_, reject) => 
            setTimeout(() => reject(new Error('Request timeout')), timeout)
        )
    ]);
}


function searchAsset(asset){
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("assetListContent").innerHTML = this.responseText;
        }
    }
    xhttp.open("GET", "assets_search.php?asset="+asset, true);
    xhttp.send();
}


function modifyPosition(transactionId) {
    modalModifyPosition.style.display = 'flex';
}

function modifyTakeProfitStopLoss(transactionId) {
    document.getElementById("modalStopLossTakeProfit").style.display = 'flex';
}

function modifyFees(transactionId) {
    document.getElementById("modalModifyFees").style.display = 'flex';
}