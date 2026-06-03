<?php
$pageTitle = 'Gestion des clients';
$currentPage = 'clients';
ob_start();
?>

<div class="page-header">
    <div>
        <div class="page-title">Gestion des clients</div>
        <div class="page-sub"><?= count($clients) ?> clients actifs</div>
    </div>
    <div style="display:flex;gap:8px">
        <div class="search-bar">
            <i class="ti ti-search"></i>
            <input type="text" id="search-clients" placeholder="Nom, téléphone, NIF…" style="border:none;background:none;outline:none;width:100%">
        </div>
        <button class="tb-btn btn-primary" onclick="openClientForm()">
            <i class="ti ti-plus"></i> Nouveau client
        </button>
    </div>
</div>

<div class="pill-nav">
    <div class="pill active-pill" onclick="filterClients('all')">Tous (<?= count($clients) ?>)</div>
    <div class="pill" onclick="filterClients('entreprise')">Entreprises</div>
    <div class="pill" onclick="filterClients('particulier')">Particuliers</div>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <table id="clients-table">
        <thead>
            <tr>
                <th style="width:36px"></th>
                <th>Client</th>
                <th>Type</th>
                <th>Téléphone</th>
                <th>CA total</th>
                <th>Solde dû</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="clients-tbody">
            <!-- Chargé en AJAX -->
        </tbody>
    </table>
</div>

<!-- Modal Formulaire Client -->
<div id="client-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
    <div class="card" style="width:90%;max-width:500px;margin:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h3 style="font-size:15px;font-weight:700;color:var(--dark)">Nouveau client</h3>
            <button style="background:none;border:none;font-size:18px;cursor:pointer" onclick="closeClientForm()">×</button>
        </div>

        <form id="client-form" onsubmit="saveClient(event)">
            <div class="form-row">
                <div class="field">
                    <label>Nom du client *</label>
                    <input type="text" name="Nom_Client" required>
                </div>
                <div class="field">
                    <label>Type *</label>
                    <select name="Type_Client" required>
                        <option value="Entreprise">Entreprise</option>
                        <option value="Particulier">Particulier</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>NIF</label>
                    <input type="text" name="NIF">
                </div>
                <div class="field">
                    <label>RCCM</label>
                    <input type="text" name="RCCM">
                </div>
            </div>

            <div class="field" style="margin-bottom:12px">
                <label>Adresse *</label>
                <input type="text" name="Adresse" required>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>Téléphone *</label>
                    <input type="tel" name="Telephone" required>
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="Email">
                </div>
            </div>

            <div class="field" style="margin-bottom:16px">
                <label>Personne de contact</label>
                <input type="text" name="Personne_Contact">
            </div>

            <div style="display:flex;gap:8px">
                <button type="submit" class="tb-btn btn-primary" style="flex:1">
                    <i class="ti ti-check"></i> Enregistrer
                </button>
                <button type="button" class="tb-btn" style="flex:1" onclick="closeClientForm()">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const clientList = new ListHandler('#clients-table');

document.addEventListener('DOMContentLoaded', () => {
    loadClients();
    
    document.getElementById('search-clients').addEventListener('input', (e) => {
        searchClients(e.target.value);
    });
});

async function loadClients() {
    try {
        const result = await apiCall('clients');
        renderClients(result.data || []);
    } catch (error) {
        console.error('Erreur chargement clients:', error);
    }
}

function renderClients(clients) {
    const tbody = document.getElementById('clients-tbody');
    tbody.innerHTML = '';

    if (!clients || clients.length === 0) {
        tbody.innerHTML = '<tr><td colspan="100%" style="text-align:center;color:var(--muted)">Aucun client</td></tr>';
        return;
    }

    clients.forEach(client => {
        const typeColor = client.Type_Client === 'Entreprise' ? 'badge-info' : 'badge-orange';
        const avatarInitials = client.Nom_Client.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><div class="client-avatar" style="background:var(--bl3);color:var(--bl2)">${avatarInitials}</div></td>
            <td>
                <div style="font-weight:600;cursor:pointer;color:var(--bl)" onclick="viewClient(${client.ID_Client})">
                    ${client.Nom_Client}
                </div>
                <div style="font-size:11px;color:var(--muted)">${client.NIF ? 'NIF: ' + client.NIF : 'Particulier'}</div>
            </td>
            <td><span class="badge ${typeColor}">${client.Type_Client}</span></td>
            <td>${client.Telephone}</td>
            <td style="font-weight:600">${formatCurrency(0)}</td>
            <td style="color:var(--warn);font-weight:600">${formatCurrency(0)}</td>
            <td>
                <button class="tb-btn btn-sm" onclick="editClient(${client.ID_Client})">
                    <i class="ti ti-edit"></i>
                </button>
                <button class="tb-btn btn-sm" style="color:var(--danger)" onclick="deleteClient(${client.ID_Client})">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function openClientForm() {
    document.getElementById('client-modal').style.display = 'flex';
    document.getElementById('client-form').reset();
}

function closeClientForm() {
    document.getElementById('client-modal').style.display = 'none';
}

async function saveClient(event) {
    event.preventDefault();
    
    const form = new FormHandler('client-form');
    const data = form.getData();

    try {
        const result = await apiCall('clients/create', 'POST', data);
        showSuccess('Client créé avec succès');
        closeClientForm();
        loadClients();
    } catch (error) {
        console.error('Erreur création client:', error);
    }
}

async function searchClients(term) {
    if (!term) {
        loadClients();
        return;
    }

    try {
        const result = await apiCall(`clients/search?q=${encodeURIComponent(term)}`);
        renderClients(result.data || []);
    } catch (error) {
        console.error('Erreur recherche:', error);
    }
}

function viewClient(id) {
    window.location = `<?= BASE_URL ?>clients/${id}`;
}

function editClient(id) {
    window.location = `<?= BASE_URL ?>clients/${id}/edit`;
}

async function deleteClient(id) {
    if (!confirmDelete('Êtes-vous sûr de vouloir désactiver ce client ?')) {
        return;
    }

    try {
        await apiCall(`clients/${id}`, 'DELETE');
        showSuccess('Client désactivé');
        loadClients();
    } catch (error) {
        console.error('Erreur suppression:', error);
    }
}

function filterClients(type) {
    // À implémenter selon les besoins
}
</script>

<?php
$content = ob_get_clean();
include ROOT_PATH . 'app/Views/layouts/main.php';
?>