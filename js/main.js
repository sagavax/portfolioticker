    const KEY_TX = 'pm_tx_v1';
    const KEY_PRICES = 'pm_prices_v1'; // { symbol: priceInBase }
    const BASE = 'EUR';

    // --- Brokers/Providers ---
    const BROKERS = {
        ROBINHOOD: 'Robinhood',
        XTB: 'XTB',
        ETORO: 'eToro',
        IBKR: 'Interactive Brokers',
        TRADING212: 'Trading 212',
        REVOLUT: 'Revolut',
        DEGIRO: 'DEGIRO',
        OANDA: 'OANDA',
        BINANCE: 'Binance',
        PIONEX: 'Pionex'
    };

    const load = (k, fallback) => JSON.parse(localStorage.getItem(k) || JSON.stringify(fallback));
    const save = (k, v) => localStorage.setItem(k, JSON.stringify(v));

    // --- Data ---
    let tx = load(KEY_TX, []);
    // Migration: remove legacy `fee` property from stored transactions (one-time)
    (function migrateRemoveFee() {
      if (!Array.isArray(tx) || tx.length === 0) return;
      let migrated = false;
      tx = tx.map(item => {
        if (item && Object.prototype.hasOwnProperty.call(item, 'fee')) {
          const copy = Object.assign({}, item);
          delete copy.fee;
          migrated = true;
          return copy;
        }
        return item;
      });
      if (migrated) {
        save(KEY_TX, tx);
        console.info('Migration: removed legacy "fee" from transactions');
      }
    })();
    let prices = load(KEY_PRICES, { AAPL: 180, MSFT: 420, BTC: 60000, SPY: 520 }); // demo ceny v EUR

    // --- FX (demo, fixné) ---
    const FX = { EUR:1, USD:0.92, GBP:1.17, CZK:0.041 };
    const toBase = (amount, ccy) => amount * (FX[ccy] ?? 1);

    const el = (q) => document.querySelector(q);
    const tbodyTx = el('#txTable tbody');

    // Inline editing: qty and price via contenteditable cells
    if (tbodyTx) {
      // store original value on focus
      tbodyTx.addEventListener('focusin', (e) => {
        const td = e.target.closest && e.target.closest('td[data-field]');
        if (td) td.dataset.original = (td.textContent || '').toString().trim();
      });

      // save on focusout
      tbodyTx.addEventListener('focusout', (e) => {
        const td = e.target.closest && e.target.closest('td[data-field]');
        if (!td) return;
        const id = td.dataset.id;
        const field = td.dataset.field;
        if (!id || !field) return;
        const raw = (td.textContent || '').toString().trim();
        // normalize comma decimal
        const parsed = parseFloat(raw.replace(',', '.'));
        if (!isFinite(parsed)) {
          // revert
          td.textContent = td.dataset.original || '';
          alert('Neplatné číslo. Úprava zrušená.');
          return;
        }
        const item = tx.find(t => t.id === id);
        if (!item) return;
        if (field === 'qty') item.qty = parsed;
        else if (field === 'price') item.price = parsed;
        save(KEY_TX, tx);
        // re-render to keep table consistent (and recalc derived values if any)
        loadPortfolio();
      });

      // allow Enter to commit edit
      tbodyTx.addEventListener('keydown', (e) => {
        const td = e.target.closest && e.target.closest('td[data-field]');
        if (!td) return;
        if (e.key === 'Enter') {
          e.preventDefault();
          // remove caret / commit by blurring
          td.blur();
        }
      });
    }

    const fmt = (n) => isFinite(n) ? new Intl.NumberFormat('sk-SK', { style:'currency', currency: BASE }).format(n) : '—';
    const pct = (pnl, cost) => isFinite(pnl) && cost>0 ? (pnl/cost*100).toFixed(2)+'%' : '—';
    const round = (n) => +(+n).toFixed(6);

    



    // Initialize provider select with options from BROKERS object
    const providerSelect = el('select[name="provider"]');
    if (providerSelect) {
        providerSelect.innerHTML = Object.values(BROKERS)
            .map(broker => `<option value="${broker}">${broker}</option>`)
            .join('');
    }

    document.addEventListener('click', (event) => {
      if(event.target.tagName === 'BUTTON') {
        if (event.target.getAttribute('data-del-por')) {
            //delete portfolio item
            removeFromPortfolio(event.target.getAttribute('data-del-por'));
        }          
      }
    });


    // --- Events ---
    //save to portfolio
    const txForm = el('#txForm');
    if (txForm) {
      txForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const f = Object.fromEntries(new FormData(e.target).entries());

        // simple validation: require non-empty ticker for BUY/SELL/DIVIDEND
        const type = (f.type || '').toUpperCase();
        let symbol = (f.symbol || '').toString().trim().toUpperCase();
        const requiresSymbol = ['BUY', 'SELL', 'DIVIDEND'];
        if (requiresSymbol.includes(type) && !symbol) {
          alert('Ticker must not be empty for ' + type + ' transactions.');
          const symEl = el('input[name="symbol"]'); if (symEl) symEl.focus();
          return;
        }

        // fee is removed from model; do not pass it
        saveToPortfolio(
          f.date || new Date().toISOString().slice(0,10),
          f.provider,
          type,
          symbol,
          f.qty,
          f.price,
          f.ccy || BASE
        );
        e.target.reset();
      });
    }

    tbodyTx.addEventListener('click', (e) => {
      const delId = e.target?.dataset?.del;
      if (delId) {
        tx = tx.filter(t => t.id !== delId);
        save(KEY_TX, tx);
        loadPortfolio(); // refresh
        return;
      }
      const noteId = e.target?.dataset?.note;
      if (noteId) {
        // open note add flow for this portfolio item
        AddNote(noteId);
        return;
      }
      // badge click: show notes for that transaction
      if (e.target && e.target.classList && e.target.classList.contains('note-count')) {
        const badgeId = e.target.dataset.id;
        showNotesList(badgeId);
      }
    });

    // Demo seeding removed; only load existing transactions from storage
    // always populate table when script runs
    loadPortfolio();

   /*  render(); */


/**
 * Sends a POST request to portfolio_add.php to add a new transaction to the portfolio.
 * @param {string} date - date of the transaction
 * @param {string} provider - broker/provider of the transaction
 * @param {string} type - type of the transaction (BUY, SELL, DEPOSIT, WITHDRAW, DIVIDEND, FEE)
 * @param {string} symbol - ticker of the stock
 * @param {number} qty - quantity of the stock
 * @param {number} price - price of the stock
 * @param {string} ccy - currency of the transaction
 */
    function saveToPortfolio(date, provider, type, symbol, qty, price, ccy) {
      // Create new transaction object
      const transaction = {
        id: crypto.randomUUID(),
        date,
        provider,
        type,
        symbol: symbol ? symbol.toUpperCase() : '',
        qty: +qty || 0,
        price: +price || 0,
        ccy: ccy || BASE
      };

      // Save to localStorage
      tx.push(transaction);
      save(KEY_TX, tx);

      // Refresh the UI
      loadPortfolio();

      // OPTIONAL: if you want to also send to server, uncomment and adapt below
      /*
      const xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          // server saved OK
        }
      };
      xhttp.open("POST", "portfolio_add.php", true);
      xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      const formData = new URLSearchParams({
        date_of_transaction: date,
        provider: provider,
        type_of_transaction: type,
        ticker: symbol || '',
        quanty: qty || '0',
        price: price || '0',
        fee: fee || '0',
        currency: ccy || BASE
      }).toString();
      xhttp.send(formData);
      */
    }


/**
 * Sends a POST request to portfolio_delete.php to delete a transaction from the portfolio.
 * @param {string} id - id of the transaction to be deleted
 */
    function removeFromPortfolio(id) {
      const xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          document.getElementById("txTable").innerHTML = this.responseText;
        }
      };
      xhttp.open("POST", "portfolio_delete.php", true);
      xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhttp.send(`&id=${id}`);
    }


/**
 * Loads the portfolio from localStorage and displays it in the #txTable element.
 */
    function loadPortfolio() {
      // Get transactions from localStorage
      const transactions = load(KEY_TX, []);
      
      // Clear existing table content
      const tbody = document.querySelector('#txTable tbody');
      if (!tbody) return;
      
      // Generate table rows for each transaction (match table columns: date, provider, type, ticker, qty, price, fee, ccy, actions)
      tbody.innerHTML = transactions.map(t => `
        <tr>
          <td>${t.date || ''}</td>
          <td>${t.provider || ''}</td>
          <td>${t.type || ''}</td>
          <td>${t.symbol || ''}</td>
          <td data-id="${t.id}" data-field="qty" contenteditable="true" class="editable-cell">${t.qty || ''}</td>
          <td data-id="${t.id}" data-field="price" contenteditable="true" class="editable-cell">${isFinite(t.price) ? t.price : ''}</td>
          <td>${t.ccy || ''}</td>
              <td class="right">
                  <span class="note-count" data-id="${t.id}" title="${(t.notes && t.notes.length ? (t.notes[0].text||'') : '') .replace(/"/g,'\"')}">${(t.notes && t.notes.length) ? t.notes.length : 0}</span>
                <button data-note="${t.id}" class="secondary">Pridať poznámku</button>
                <button data-del="${t.id}" class="secondary">Zmaž</button>
              </td>
        </tr>
      `).join('');
    }


    // Open modal to add/edit note for a transaction
    function renderNotesInModal(portfolitemId) {
      const listEl = el('#noteExistingList');
      if (!listEl) return;
      const item = tx.find(t => t.id === portfolitemId);
      if (!item || !item.notes || item.notes.length === 0) {
        listEl.innerHTML = '<div class="muted">Žiadne poznámky</div>';
        return;
      }
      listEl.innerHTML = item.notes.map((n, idx) => {
        const date = n.createdAt ? new Date(n.createdAt).toLocaleString() : '';
        const text = String(n.text || '').replace(/\n/g, '<br>');
        return `<div style="padding:6px 0;border-bottom:1px solid #eee"><div style="font-size:12px;color:#666;margin-bottom:6px">${date} <button data-editnote="${idx}" data-id="${item.id}" style="margin-left:8px;" class="secondary">Upraviť</button><button data-delnote="${idx}" data-id="${item.id}" style="margin-left:8px;" class="secondary">Zmazať</button></div><div>${text}</div></div>`;
      }).join('');
    }

    function AddNote(portfolitemId) {
      const modal = el('#noteModal');
      const textarea = el('#noteText');
      if (!modal || !textarea) return;
      // ensure notes array exists
      const item = tx.find(t => t.id === portfolitemId);
      if (item && !item.notes) item.notes = [];
      renderNotesInModal(portfolitemId);
      // always start with empty textarea for a new note
      textarea.value = '';
      if (modal.dataset.editIndex) delete modal.dataset.editIndex;
      modal.dataset.currentId = portfolitemId;
      modal.style.display = 'flex';
      textarea.focus();
    }

    // Hook up modal buttons (save / cancel) if present
    const noteSaveBtn = el('#noteSave');
    const noteCancelBtn = el('#noteCancel');
    const noteModal = el('#noteModal');
    if (noteSaveBtn) {
      noteSaveBtn.addEventListener('click', () => {
        const id = noteModal?.dataset?.currentId;
        const text = (el('#noteText')?.value || '').toString().trim();
        if (!id) return;
        if (!text) { alert('Poznámka je prázdna.'); return; }
        const item = tx.find(t => t.id === id);
        if (!item) return;
        if (!item.notes) item.notes = [];
        // check if editing existing note
        const editIdx = noteModal?.dataset?.editIndex;
        if (typeof editIdx !== 'undefined') {
          const idx = parseInt(editIdx, 10);
          if (!isNaN(idx) && idx >= 0 && idx < item.notes.length) {
            item.notes[idx].text = text;
            item.notes[idx].modifiedAt = new Date().toISOString();
            save(KEY_TX, tx);
            loadPortfolio();
            renderNotesInModal(id);
            // clear edit state and textarea
            delete noteModal.dataset.editIndex;
            if (el('#noteText')) el('#noteText').value = '';
            return;
          }
        }
        // otherwise add new note
        item.notes.push({ text, createdAt: new Date().toISOString() });
        save(KEY_TX, tx);
        // refresh UI and modal list
        loadPortfolio();
        renderNotesInModal(id);
        // clear textarea but keep modal open for adding more
        if (el('#noteText')) el('#noteText').value = '';
      });
    }
    if (noteCancelBtn) {
      noteCancelBtn.addEventListener('click', () => {
        // clear textarea and close
        if (el('#noteText')) el('#noteText').value = '';
        if (noteModal) {
          delete noteModal.dataset.currentId;
          noteModal.style.display = 'none';
        }
      });
    }
    if (noteModal) {
      noteModal.addEventListener('click', (e) => {
        if (e.target === noteModal) {
          if (el('#noteText')) el('#noteText').value = '';
          delete noteModal.dataset.currentId;
          noteModal.style.display = 'none';
        }
      });
    }

    // Handle delete buttons inside noteExistingList
    const noteExistingList = el('#noteExistingList');
    if (noteExistingList) {
      noteExistingList.addEventListener('click', (e) => {
        const delIdx = e.target?.dataset?.delnote;
        const parentId = e.target?.dataset?.id;
        if (typeof delIdx !== 'undefined' && parentId) {
          const item = tx.find(t => t.id === parentId);
          if (!item || !item.notes) return;
          const idx = parseInt(delIdx, 10);
          if (!isNaN(idx) && idx >= 0 && idx < item.notes.length) {
            if (!confirm('Naozaj zmazať túto poznámku?')) return;
            item.notes.splice(idx, 1);
            save(KEY_TX, tx);
            renderNotesInModal(parentId);
            loadPortfolio();
          }
        }
      });
    }

    // Notes list modal wiring
    const notesListModal = el('#notesListModal');
    const notesListContent = el('#notesListContent');
    const notesListClose = el('#notesListClose');
    function showNotesList(id) {
      if (id) {
        const item = tx.find(t => t.id === id);
        if (!item || !item.notes || item.notes.length === 0) {
          alert('Žiadne poznámky na zobrazenie pre túto transakciu.');
          return;
        }
        const html = item.notes.map(n => {
          const header = `${item.date || ''} — ${item.provider || ''} ${item.symbol ? '(' + item.symbol + ')' : ''}`;
          const body = String(n.text || n).replace(/\n/g, '<br>');
          const date = n.createdAt ? new Date(n.createdAt).toLocaleString() : '';
          return `<div style="padding:8px 0;border-bottom:1px solid #eee"><div style="font-size:12px;color:#666">${date}</div><div style="margin-top:6px">${body}</div></div>`;
        }).join('');
        if (notesListContent) notesListContent.innerHTML = html;
        if (notesListModal) notesListModal.style.display = 'flex';
        return;
      }
      // fallback: show all notes across transactions
      const notes = tx.filter(t => t.notes && t.notes.length>0);
      if (!notes || notes.length === 0) {
        alert('Žiadne poznámky na zobrazenie.');
        return;
      }
      const html = notes.map(n => {
        const header = `${n.date || ''} — ${n.provider || ''} ${n.symbol ? '(' + n.symbol + ')' : ''}`;
        const body = n.notes.map(nn => (String(nn.text||nn).replace(/\n/g, '<br>'))).join('<hr>');
        return `<div style="padding:8px 0;border-bottom:1px solid #eee"><strong>${header}</strong><div style="margin-top:6px">${body}</div></div>`;
      }).join('');
      if (notesListContent) notesListContent.innerHTML = html;
      if (notesListModal) notesListModal.style.display = 'flex';
    }
    if (notesListClose) notesListClose.addEventListener('click', () => { if (notesListModal) notesListModal.style.display = 'none'; });
    if (notesListModal) notesListModal.addEventListener('click', (e) => { if (e.target === notesListModal) notesListModal.style.display = 'none'; });