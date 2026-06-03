<?php
$pageTitle = 'Trésorerie';
$currentPage = 'tresorerie';
ob_start();
?>

<div class="page-header">
    <div>
        <div class="page-title">Trésorerie</div>
        <div class="page-sub">Bilan financier du mois</div>
    </div>
    <div style="display:flex;gap:8px">
        <select id="month-select" onchange="loadTresorerie()" style="border:1px solid var(--border);border-radius:var(--rads);padding:6px 10px;font-size:12.5px">
            <option value="current">Mois courant</option>
            <option value="last">Mois dernier</option>
            <option value="3months">3 derniers mois</option>
            <option value="year">Cette année</option>
        </select>
        <button class="tb-btn btn-primary">
            <i class="ti ti-download"></i> Export PDF
        </button>
    </div>
</div>

<div class="tresorerie-row">
    <div class="tresos-card">
        <div class="tresos-icon" style="background:#d1fae5">
            <i class="ti ti-trending-up" style="font-size:20px;color:#065f46"></i>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:var(--success)" id="encaisse-total">0 FCFA</div>
            <div style="font-size:11px;color:var(--muted)">Encaissements</div>
        </div>
    </div>
    <div class="tresos-card">
        <div class="tresos-icon" style="background:#fee2e2">
            <i class="ti ti-trending-down" style="font-size:20px;color:#991b1b"></i>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:var(--danger)" id="depense-total">0 FCFA</div>
            <div style="font-size:11px;color:var(--muted)">Décaissements</div>
        </div>
    </div>
    <div class="tresos-card" style="border:2px solid var(--bl)">
        <div class="tresos-icon" style="background:var(--bl3)">
            <i class="ti ti-wallet" style="font-size:20px;color:var(--bl2)"></i>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:var(--bl)" id="resultat-net">0 FCFA</div>
            <div style="font-size:11px;color:var(--muted)">Résultat net</div>
        </div>
    </div>
</div>

<div class="grid2">
    <div class="card">
        <div class="card-head">
            <span class="card-title">Encaissements par mode</span>
        </div>
        <div class="mini-list" id="encaissements-list">
            <!-- Chargé en AJAX -->
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <span class="card-title">Dépenses du mois</span>
            <button class="tb-btn btn-sm btn-orange" onclick="addExpense()">
                <i class="ti ti-plus"></i> Ajouter
            </button>
        </div>
        <div class="mini-list" id="depenses-list">
            <!-- Chargé en AJAX -->
        </div>
    </div>
</div>

<div class="card" style="margin-top:14px">
    <div class="card-title" style="margin-bottom:12px">Détail des paiements reçus</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Facture</th>
                <th>Client</th>
                <th>Mode</th>
                <th>Montant</th>
            </tr>
        </thead>
        <tbody id="paiements-list">
            <!-- Chargé en AJAX -->
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadTresorerie();
});

async function loadTresorerie() {
    try {
        // Charger les statistiques
        const result = await apiCall('tresorerie');
        
        // Encaissements
        document.getElementById('encaisse-total').textContent = formatCurrency(result.data.encaisse || 0);
        
        // Dépenses
        document.getElementById('depense-total').textContent = formatCurrency(result.data.depenses || 0);
        
        // Résultat net
        const net = (result.data.encaisse || 0) - (result.data.depenses || 0);
        document.getElementById('resultat-net').textContent = formatCurrency(net);
        
        // Encaissements par mode
        renderEncaissements(result.data.encaissements_mode || []);
        
        // Dépenses
        renderDepenses(result.data.depenses_detail || []);
        
        // Paiements
        renderPaiements(result.data.paiements || []);
        
    } catch (error) {
        console.error('Erreur trésorerie:', error);
    }
}

function renderEncaissements(encaissements) {
    const container = document.getElementById('encaissements-list');
    container.innerHTML = '';

    const modes = [
        { label: 'Espèces', color: 'var(--success)' },
        { label: 'Orange Money', color: 'var(--or)' },
        { label: 'Virement', color: 'var(--bl2)' },
        { label: 'Chèque', color: 'var(--mid)' }
    ];

    modes.forEach(mode => {
        const total = encaissements.find(e => e.Mode_Paiement === mode.label)?.total || 0;
        const percent = 100; // À calculer avec total général

        const row = document.createElement('div');
        row.className = 'mini-row';
        row.innerHTML = `
            <span>${mode.label}</span>
            <span style="font-weight:600;color:${mode.color}">${formatCurrency(total)}</span>
            <div style="width:80px">
                <div class="progress-bar">
                    <div class="progress-fill" style="width:${percent}%;background:${mode.color}"></div>
                </div>
            </div>
        `;
        container.appendChild(row);
    });
}

function renderDepenses(depenses) {
    const container = document.getElementById('depenses-list');
    container.innerHTML = '';

    if (!depenses || depenses.length === 0) {
        container.innerHTML = '<div style="padding:12px;text-align:center;color:var(--muted);font-size:12px">Aucune dépense</div>';
        return;
    }

    depenses.forEach(depense => {
        const row = document.createElement('div');
        row.className = 'mini-row';
        row.innerHTML = `
            <span>${depense.Libelle}</span>
            <span style="font-weight:600;color:var(--danger)">${formatCurrency(depense.Montant)}</span>
        `;
        container.appendChild(row);
    });
}

function renderPaiements(paiements) {
    const tbody = document.getElementById('paiements-list');
    tbody.innerHTML = '';

    if (!paiements || paiements.length === 0) {
        tbody.innerHTML = '<tr><td colspan="100%" style="text-align:center;color:var(--muted)">Aucun paiement</td></tr>';
        return;
    }

    paiements.forEach(paiement => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="color:var(--muted);font-size:11.5px">${formatDate(paiement.Date_Paiement)}</td>
            <td style="font-weight:600;color:var(--bl)">${paiement.Reference}</td>
            <td>${paiement.Nom_Client}</td>
            <td style="font-size:11.5px;color:var(--muted)">${paiement.Mode_Paiement}</td>
            <td style="font-weight:600;color:var(--success)">${formatCurrency(paiement.Montant)}</td>
        `;
        tbody.appendChild(tr);
    });
}

function addExpense() {
    const libelle = prompt('Description de la dépense:');
    if (!libelle) return;

    const montant = prompt('Montant (FCFA):');
    if (!montant) return;

    const categorie = prompt('Catégorie (Fournitures, Loyer, Transport, etc.):');

    apiCall('depenses/create', 'POST', {
        Libelle: libelle,
        Montant: parseFloat(montant),
        Categorie: categorie,
        Date_Depense: new Date().toISOString()
    })
    .then(result => {
        showSuccess('Dépense enregistrée');
        loadTresorerie();
    })
    .catch(error => console.error('Erreur:', error));
}
</script>

<?php
$content = ob_get_clean();
include ROOT_PATH . 'app/Views/layouts/main.php';
?>