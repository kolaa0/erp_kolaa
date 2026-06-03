<?php
$pageTitle = 'Proformas';
$currentPage = 'proformas';
ob_start();
?>

<div class="page-header">
    <div>
        <div class="page-title">Proformas (Devis)</div>
        <div class="page-sub">Suivi des devis émis</div>
    </div>
    <div style="display:flex;gap:8px">
        <div class="search-bar">
            <i class="ti ti-search"></i>
            <input type="text" id="search-proforma" placeholder="Référence, client…" style="border:none;background:none;outline:none;width:100%">
        </div>
        <button class="tb-btn btn-primary" onclick="openProformaForm()">
            <i class="ti ti-plus"></i> Nouveau proforma
        </button>
    </div>
</div>

<div class="pill-nav">
    <div class="pill active-pill" onclick="filterProforma('all')">Tous</div>
    <div class="pill" onclick="filterProforma('EN_ATTENTE')">En attente</div>
    <div class="pill" onclick="filterProforma('ACCEPTE')">Acceptés</div>
    <div class="pill" onclick="filterProforma('CONVERTI')">Convertis</div>
    <div class="pill" onclick="filterProforma('REFUSE')">Refusés</div>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <table id="proforma-table">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Client</th>
                <th>Objet</th>
                <th>Montant TTC</th>
                <th>Émis le</th>
                <th>Validité</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="proforma-tbody">
            <!-- Chargé en AJAX -->
        </tbody>
    </table>
</div>

<!-- Modal Formulaire Proforma -->
<div id="proforma-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;overflow-y:auto;padding:20px">
    <div class="card" style="width:90%;max-width:900px;margin:20px auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h3 style="font-size:15px;font-weight:700;color:var(--dark)">Nouveau proforma</h3>
            <button style="background:none;border:none;font-size:18px;cursor:pointer" onclick="closeProformaForm()">×</button>
        </div>

        <form id="proforma-form" onsubmit="saveProforma(event)">
            <div class="grid2">
                <div class="card">
                    <div class="card-title" style="margin-bottom:12px">Informations générales</div>
                    
                    <div class="form-row">
                        <div class="field">
                            <label>Client *</label>
                            <select name="ID_Client" id="proforma-client" required>
                                <option value="">— Sélectionner un client —</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Objet *</label>
                            <input type="text" name="Objet" placeholder="Description du projet" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label>Date d'émission</label>
                            <input type="date" name="Date_Emission" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="field">
                            <label>Validité jusqu'au</label>
                            <input type="date" name="Date_Validite">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label>TVA (%)</label>
                            <select name="Taux_TVA">
                                <option value="0">0 %</option>
                                <option value="18" selected>18 %</option>
                                <option value="5.5">5.5 %</option>
                            </select>
                        </div>
                        <div class="field"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title" style="margin-bottom:12px">Totaux</div>
                    <div style="display:flex;flex-direction:column;gap:8px;font-size:13px">
                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border)">
                            <span style="color:var(--muted)">Sous-total HT</span>
                            <span id="montant-ht" style="font-weight:600">0 FCFA</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border)">
                            <span style="color:var(--muted)">TVA</span>
                            <span id="montant-tva" style="font-weight:600">0 FCFA</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:8px 10px;background:var(--bl3);border-radius:var(--rads);margin-top:4px">
                            <span style="font-weight:700;color:var(--dark)">Total TTC</span>
                            <span id="montant-ttc" style="font-weight:700;font-size:15px;color:var(--bl)">0 FCFA</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lignes -->
            <div class="card" style="padding:0;overflow:hidden;margin-bottom:12px">
                <div style="padding:12px 16px;background:var(--bl);display:flex;align-items:center;justify-content:space-between">
                    <span style="color:white;font-size:13px;font-weight:600">Lignes de prestations</span>
                    <button type="button" class="tb-btn btn-sm" style="background:var(--or);color:white;border-color:var(--or)" onclick="addProformaLine()">
                        <i class="ti ti-plus"></i> Ajouter ligne
                    </button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:30px">#</th>
                            <th>Désignation</th>
                            <th style="width:80px">Qté</th>
                            <th style="width:100px">Prix unitaire</th>
                            <th style="width:100px">Total</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="proforma-lines">
                        <!-- Lignes ajoutées ici -->
                    </tbody>
                </table>
            </div>

            <div style="display:flex;gap:8px">
                <button type="submit" class="tb-btn btn-primary" style="flex:1">
                    <i class="ti ti-check"></i> Enregistrer
                </button>
                <button type="button" class="tb-btn" style="flex:1" onclick="closeProformaForm()">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let proformaLineCount = 0;
let proformaClients = [];

document.addEventListener('DOMContentLoaded', async () => {
    loadProformas();
    loadClientsForSelect();
    
    document.getElementById('search-proforma').addEventListener('input', (e) => {
        searchProforma(e.target.value);
    });
});

async function loadProformas() {
    try {
        const result = await apiCall('proformas');
        renderProformas(result.data || []);
    } catch (error) {
        console.error('Erreur chargement proformas:', error);
    }
}

async function loadClientsForSelect() {
    try {
        const result = await apiCall('clients');
        const select = document.getElementById('proforma-client');
        
        proformaClients = result.data || [];
        select.innerHTML = '<option value="">— Sélectionner un client —</option>';
        
        proformaClients.forEach(client => {
            const option = document.createElement('option');
            option.value = client.ID_Client;
            option.textContent = client.Nom_Client;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Erreur chargement clients:', error);
    }
}

function renderProformas(proformas) {
    const tbody = document.getElementById('proforma-tbody');
    tbody.innerHTML = '';

    if (!proformas || proformas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="100%" style="text-align:center;color:var(--muted)">Aucun proforma</td></tr>';
        return;
    }

    proformas.forEach(pf => {
        const statusColor = {
            'EN_ATTENTE': 'badge-warn',
            'ACCEPTE': 'badge-success',
            'CONVERTI': 'badge-info',
            'REFUSE': 'badge-danger'
        }[pf.Statut] || 'badge-info';

        const statusText = {
            'EN_ATTENTE': 'En attente',
            'ACCEPTE': 'Accepté',
            'CONVERTI': 'Converti',
            'REFUSE': 'Refusé'
        }[pf.Statut];

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight:600;color:var(--bl);cursor:pointer" onclick="viewProforma(${pf.ID_Proforma})">
                ${pf.Reference}
            </td>
            <td>${pf.Nom_Client || 'Client'}</td>
            <td style="font-size:11.5px;color:var(--muted)">${pf.Objet}</td>
            <td style="font-weight:600">${formatCurrency(pf.Montant_TTC)}</td>
            <td style="color:var(--muted);font-size:11.5px">${formatDate(pf.Date_Emission)}</td>
            <td style="color:var(--muted);font-size:11.5px">${pf.Date_Validite ? formatDate(pf.Date_Validite) : '-'}</td>
            <td><span class="badge ${statusColor}">${statusText}</span></td>
            <td>
                <button class="tb-btn btn-sm" onclick="viewProforma(${pf.ID_Proforma})">
                    <i class="ti ti-eye"></i>
                </button>
                ${pf.Statut === 'EN_ATTENTE' ? `
                    <button class="tb-btn btn-sm btn-orange" onclick="convertToInvoice(${pf.ID_Proforma})">
                        <i class="ti ti-arrow-right"></i>
                    </button>
                ` : ''}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function openProformaForm() {
    proformaLineCount = 0;
    document.getElementById('proforma-form').reset();
    document.getElementById('proforma-lines').innerHTML = '';
    addProformaLine();
    document.getElementById('proforma-modal').style.display = 'flex';
}

function closeProformaForm() {
    document.getElementById('proforma-modal').style.display = 'none';
}

function addProformaLine() {
    const lineNum = ++proformaLineCount;
    const line = document.createElement('tr');
    line.id = `line-${lineNum}`;
    line.innerHTML = `
        <td style="color:var(--muted)">${lineNum}</td>
        <td>
            <select name="service_${lineNum}" class="proforma-service" onchange="setServicePrice(${lineNum})" style="border:none;background:none;font-size:12.5px;width:100%;padding:4px 0">
                <option value="">— Catalogue ou saisie libre —</option>
                <option value="Conception graphique">Conception graphique</option>
                <option value="Impression Bâche">Impression Bâche</option>
                <option value="Carte de visite">Carte de visite</option>
                <option value="Roll Up">Roll Up</option>
                <option value="Flyer / Dépliant">Flyer / Dépliant</option>
            </select>
            <input type="text" name="designation_${lineNum}" placeholder="Ou saisie libre" style="border:1px solid var(--border);border-radius:4px;padding:4px 6px;width:100%;margin-top:4px">
        </td>
        <td><input type="number" name="qty_${lineNum}" value="1" min="1" step="0.01" onchange="calcLine(${lineNum})" style="width:70px;border:1px solid var(--border);border-radius:4px;padding:4px 6px"></td>
        <td><input type="number" name="price_${lineNum}" value="0" min="0" step="0.01" onchange="calcLine(${lineNum})" style="width:90px;border:1px solid var(--border);border-radius:4px;padding:4px 6px"></td>
        <td style="font-weight:600;text-align:right" id="total-${lineNum}">0</td>
        <td><button type="button" class="tb-btn btn-sm" style="color:var(--danger)" onclick="removeProformaLine(${lineNum})"><i class="ti ti-trash"></i></button></td>
    `;
    document.getElementById('proforma-lines').appendChild(line);
}

function removeProformaLine(lineNum) {
    const line = document.getElementById(`line-${lineNum}`);
    if (line) {
        line.remove();
        calcTotals();
    }
}

function setServicePrice(lineNum) {
    const service = document.querySelector(`[name="service_${lineNum}"]`).value;
    const priceMap = {
        'Conception graphique': 150000,
        'Impression Bâche': 5000,
        'Carte de visite': 25000,
        'Roll Up': 75000,
        'Flyer / Dépliant': 35000
    };
    
    if (priceMap[service]) {
        document.querySelector(`[name="price_${lineNum}"]`).value = priceMap[service];
        calcLine(lineNum);
    }
}

function calcLine(lineNum) {
    const qty = parseFloat(document.querySelector(`[name="qty_${lineNum}"]`).value) || 0;
    const price = parseFloat(document.querySelector(`[name="price_${lineNum}"]`).value) || 0;
    const total = qty * price;
    
    document.getElementById(`total-${lineNum}`).textContent = formatCurrency(total);
    calcTotals();
}

function calcTotals() {
    let totalHT = 0;
    const lines = document.querySelectorAll('#proforma-lines tr');
    
    lines.forEach((line, idx) => {
        const qty = parseFloat(line.querySelector(`[name="qty_${idx + 1}"]`)?.value) || 0;
        const price = parseFloat(line.querySelector(`[name="price_${idx + 1}"]`)?.value) || 0;
        totalHT += qty * price;
    });

    const tva = parseFloat(document.querySelector('[name="Taux_TVA"]').value) || 0;
    const montantTVA = totalHT * (tva / 100);
    const montantTTC = totalHT + montantTVA;

    document.getElementById('montant-ht').textContent = formatCurrency(totalHT);
    document.getElementById('montant-tva').textContent = formatCurrency(montantTVA);
    document.getElementById('montant-ttc').textContent = formatCurrency(montantTTC);

    // Mettre à jour les champs cachés
    document.querySelector('[name="Montant_HT"]')?.remove();
    document.querySelector('[name="Montant_TVA"]')?.remove();
    document.querySelector('[name="Montant_TTC"]')?.remove();

    const form = document.getElementById('proforma-form');
    form.innerHTML += `
        <input type="hidden" name="Montant_HT" value="${totalHT}">
        <input type="hidden" name="Montant_TVA" value="${montantTVA}">
        <input type="hidden" name="Montant_TTC" value="${montantTTC}">
    `;
}

async function saveProforma(event) {
    event.preventDefault();
    
    const form = new FormHandler('proforma-form');
    const data = form.getData();

    try {
        const result = await apiCall('proformas/create', 'POST', data);
        showSuccess('Proforma créé avec succès');
        closeProformaForm();
        loadProformas();
    } catch (error) {
        console.error('Erreur création proforma:', error);
    }
}

function viewProforma(id) {
    window.location = `<?= BASE_URL ?>proformas/${id}`;
}

async function searchProforma(term) {
    if (!term) {
        loadProformas();
        return;
    }

    try {
        const result = await apiCall(`proformas/search?q=${encodeURIComponent(term)}`);
        renderProformas(result.data || []);
    } catch (error) {
        console.error('Erreur recherche:', error);
    }
}

function filterProforma(status) {
    // À implémenter selon les besoins
}

async function convertToInvoice(id) {
    if (!confirmDelete('Convertir ce proforma en facture ?')) {
        return;
    }

    try {
        const result = await apiCall(`proformas/${id}/convert`, 'POST');
        showSuccess('Facture créée depuis le proforma');
        loadProformas();
    } catch (error) {
        console.error('Erreur conversion:', error);
    }
}
</script>

<?php
$content = ob_get_clean();
include ROOT_PATH . 'app/Views/layouts/main.php';
?>