<?php
$pageTitle = 'Factures';
$currentPage = 'factures';
ob_start();
?>

<div class="page-header">
    <div>
        <div class="page-title">Factures</div>
        <div class="page-sub">Gestion complète de la facturation</div>
    </div>
    <div style="display:flex;gap:8px">
        <div class="search-bar">
            <i class="ti ti-search"></i>
            <input type="text" id="search-facture" placeholder="Référence, client…" style="border:none;background:none;outline:none;width:100%">
        </div>
        <button class="tb-btn btn-primary" onclick="openInvoiceForm()">
            <i class="ti ti-plus"></i> Nouvelle facture
        </button>
    </div>
</div>

<div class="pill-nav">
    <div class="pill active-pill" onclick="filterInvoice('all')">Toutes</div>
    <div class="pill" onclick="filterInvoice('EN_ATTENTE')">En attente</div>
    <div class="pill" onclick="filterInvoice('PARTIELLE')">Partielles</div>
    <div class="pill" onclick="filterInvoice('SOLDEE')">Soldées</div>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <table id="factures-table">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Client</th>
                <th>Date</th>
                <th>Montant TTC</th>
                <th>Encaissé</th>
                <th>Solde</th>
                <th>Avancement</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="factures-tbody">
            <!-- Chargé en AJAX -->
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadFactures();
    
    document.getElementById('search-facture').addEventListener('input', (e) => {
        searchFacture(e.target.value);
    });
});

async function loadFactures() {
    try {
        const result = await apiCall('factures');
        renderFactures(result.data || []);
    } catch (error) {
        console.error('Erreur chargement factures:', error);
    }
}

function renderFactures(factures) {
    const tbody = document.getElementById('factures-tbody');
    tbody.innerHTML = '';

    if (!factures || factures.length === 0) {
        tbody.innerHTML = '<tr><td colspan="100%" style="text-align:center;color:var(--muted)">Aucune facture</td></tr>';
        return;
    }

    factures.forEach(facture => {
        const statusColor = {
            'EN_ATTENTE': 'badge-danger',
            'PARTIELLE': 'badge-warn',
            'SOLDEE': 'badge-success'
        }[facture.Statut] || 'badge-info';

        const statusText = {
            'EN_ATTENTE': 'En attente',
            'PARTIELLE': 'Partielle',
            'SOLDEE': 'Soldée'
        }[facture.Statut];

        const progress = facture.Montant_TTC > 0 ? (facture.Montant_Paye / facture.Montant_TTC * 100) : 0;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight:600;color:var(--bl);cursor:pointer" onclick="viewInvoice(${facture.ID_Facture})">
                ${facture.Reference}
            </td>
            <td style="color:var(--muted)">${facture.Nom_Client || 'Client'}</td>
            <td style="color:var(--muted);font-size:11.5px">${formatDate(facture.Date_Emission)}</td>
            <td style="font-weight:600">${formatCurrency(facture.Montant_TTC)}</td>
            <td style="color:var(--success)">${formatCurrency(facture.Montant_Paye)}</td>
            <td style="color:var(--warn);font-weight:600">${formatCurrency(facture.Solde_Restant)}</td>
            <td style="width:100px">
                <div class="progress-bar">
                    <div class="progress-fill" style="width:${progress}%;background:${progress === 100 ? 'var(--success)' : 'var(--or)'}"></div>
                </div>
            </td>
            <td><span class="badge ${statusColor}">${statusText}</span></td>
            <td>
                <button class="tb-btn btn-sm btn-orange" onclick="recordPayment(${facture.ID_Facture})">
                    <i class="ti ti-credit-card"></i>
                </button>
                <button class="tb-btn btn-sm" onclick="viewInvoice(${facture.ID_Facture})">
                    <i class="ti ti-download"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function viewInvoice(id) {
    window.location = `<?= BASE_URL ?>factures/${id}`;
}

async function searchFacture(term) {
    if (!term) {
        loadFactures();
        return;
    }

    try {
        const result = await apiCall(`factures/search?q=${encodeURIComponent(term)}`);
        renderFactures(result.data || []);
    } catch (error) {
        console.error('Erreur recherche:', error);
    }
}

function filterInvoice(status) {
    // À implémenter
}

function openInvoiceForm() {
    window.location = `<?= BASE_URL ?>factures/create`;
}

function recordPayment(id) {
    // Ouvrir modal de paiement
    const amount = prompt('Montant à enregistrer (FCFA):');
    if (!amount) return;

    apiCall(`paiements/create`, 'POST', {
        ID_Facture: id,
        Montant: parseFloat(amount),
        Mode_Paiement: 'Especes',
        Date_Paiement: new Date().toISOString()
    })
    .then(result => {
        showSuccess('Paiement enregistré');
        loadFactures();
    })
    .catch(error => console.error('Erreur:', error));
}
</script>

<?php
$content = ob_get_clean();
include ROOT_PATH . 'app/Views/layouts/main.php';
?>