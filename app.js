const token = localStorage.getItem('token');
if (!token) window.location.href = 'login.html';

// All currencies
const currencies = ['USD','KES','EUR','GBP','JPY','CAD','AUD','CHF'];

// Load everything on start
window.onload = () => {
    checkRates();
    loadFavorites();
    loadHistory();
};

// ==================
// CONVERSION
// ==================
async function convertCurrency() {
    const amount = document.getElementById('amount').value;
    const from   = document.getElementById('from').value;
    const to     = document.getElementById('to').value;

    if (!amount || amount <= 0) {
        document.getElementById('result').textContent = '0.00';
        return;
    }

    if (from === to) {
        document.getElementById('result').textContent =
            parseFloat(amount).toFixed(2);
        return;
    }

    document.getElementById('spinner')
            .classList.remove('hidden');

    try {
        const res = await fetch(
            `convert.php?from=${from}&to=${to} `+
            `&amount=${amount}&token=${token}`
        );
        const data = await res.json();

        if (data.error) { alert(data.error); return; }

        document.getElementById('result').textContent =
            `${to} ${parseFloat(data.converted).toFixed(2)}`;

        document.getElementById('rate-display').textContent =
           `1 ${from} = ${data.rate} ${to}`;

        document.getElementById('rate-age').textContent =
           `🕐 Rate updated: ${data.updated}`;

        // Save to DB history
        saveHistory(amount, from, to, data.converted, data.rate);

    } catch (err) {
        alert('Conversion failed. Try again.');
    } finally {
        document.getElementById('spinner')
                .classList.add('hidden');
    }
}

function swapCurrencies() {
    const f = document.getElementById('from');
    const t = document.getElementById('to');
    [f.value, t.value] = [t.value, f.value];
    convertCurrency();
}

// ==================
// HISTORY
// ==================
async function saveHistory(amount, from, to, converted, rate) {
    const formData = new FormData();
    formData.append('token',     token);
    formData.append('from',      from);
    formData.append('to',        to);
    formData.append('amount',    amount);
    formData.append('converted', converted);
    formData.append('rate',      rate);

    await fetch('save_history.php', {
        method: 'POST',
        body: formData
    });

    // Reload history display
    loadHistory();
}

async function loadHistory() {
    const res  = await fetch(`get_history.php?token=${token}`);
    const data = await res.json();
    const list = document.getElementById('history-list');

    if (!data.length) {
        list.innerHTML = '<p class="empty">No history yet</p>';
        return;
    }

    list.innerHTML = data.map(item => `
        <div class="history-item">
            <div class="history-left">
                ${item.amount} ${item.from_currency} → 
                ${parseFloat(item.converted_amount).toFixed(2)} 
                ${item.to_currency}
            </div>
            <div class="history-right">
                <div class="history-rate">
                    Rate: ${item.rate}
                </div>
                <div class="history-date">
                    ${new Date(item.created_at)
                        .toLocaleDateString()}
                </div>
            </div>
        </div>
    `).join('');
}

async function clearHistory() {
    if (!confirm('Clear all history?')) return;
    await fetch('clear_history.php', {
        method: 'POST',
        body: new URLSearchParams({ token })
    });
    loadHistory();
}

// ==================
// FAVORITES
// ==================
async function loadFavorites() {
    const res  = await fetch(
        `favorites.php?action=get&token=${token}`
    );
    const data = await res.json();
    const list = document.getElementById('favorites-list');

    if (!data.length) {
        list.innerHTML = '<p class="empty">No favorites yet</p>';
        return;
    }

    list.innerHTML = data.map(fav => `
        <div class="fav-item">
            <span 
                class="fav-pair"
                onclick="loadFavorite(
                    '${fav.from_currency}',
                    '${fav.to_currency}'
                )"
            >
                ${fav.from_currency} → ${fav.to_currency}
            </span>
            <button 
                class="fav-delete"
                onclick="deleteFavorite(${fav.id})"
            >
                🗑️
            </button>
        </div>
    `).join('');
}

async function saveFavorite() {
    const from = document.getElementById('from').value;
    const to   = document.getElementById('to').value;

    const formData = new FormData();
    formData.append('token',  token);
    formData.append('action', 'add');
    formData.append('from',   from);
    formData.append('to',     to);

    const res  = await fetch('favorites.php', {
        method: 'POST', body: formData
    });
    const data = await res.json();

    if (data.status === 'added') {
        loadFavorites();
    } else {
        alert('Already in favorites!');
    }
}

async function deleteFavorite(id) {
    const formData = new FormData();
    formData.append('token',  token);
    formData.append('action', 'delete');
    formData.append('fav_id', id);

    await fetch('favorites.php', {
        method: 'POST', body: formData
    });
    loadFavorites();
}

function loadFavorite(from, to) {
    document.getElementById('from').value = from;
    document.getElementById('to').value   = to;
    convertCurrency();
}

// ==================
// MULTI-CURRENCY
// ==================
async function multiConvert() {
    const amount = document.getElementById('multi-amount').value;
    if (!amount || amount <= 0) return;

    const results = document.getElementById('multi-results');
    results.innerHTML = '<p class="empty">Loading...</p>';

    const targets = currencies.filter(c => c !== 'USD');
    const fetches = targets.map(currency =>
        fetch(
            `convert.php?from=USD&to=${currency}` +
           `&amount=${amount}&token=${token}`
        ).then(r => r.json())
    );

    const data = await Promise.all(fetches);

    results.innerHTML = data.map((item, i) => `
        <div class="multi-item">
            <span class="multi-currency">
                ${targets[i]}
            </span>
            <span class="multi-amount">
                ${parseFloat(item.converted).toFixed(2)}
            </span>
        </div>
    `).join('');
}

// ==================
// RATE CHECK
// ==================
async function checkRates() {
    try {
        const res = await fetch(`cron_job.php?token=${token}`);
        const data = await res.json();
        console.log('Rates:', data.message);
    } catch (err) {
        console.log('Rate check failed');
    }
}

// Logout
function logout() {
    localStorage.removeItem('token');
    window.location.href = 'login.html';
}