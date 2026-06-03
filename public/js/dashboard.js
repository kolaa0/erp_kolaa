/**
 * dashboard.js - Graphiques et statistiques
 */

document.addEventListener('DOMContentLoaded', async function() {
    await initDashboard();
});

async function initDashboard() {
    try {
        // Charger les dernières factures
        await loadLatestInvoices();
        
        // Initialiser les graphiques
        initCharts();
        
    } catch (error) {
        console.error('Erreur dashboard:', error);
    }
}

/**
 * Charge les dernières factures
 */
async function loadLatestInvoices() {
    try {
        const result = await apiCall('factures?limit=3');
        const container = document.getElementById('latest-invoices');
        
        if (!container) return;
        
        container.innerHTML = '';
        
        if (!result.data || result.data.length === 0) {
            container.innerHTML = '<div style="padding:12px;text-align:center;color:var(--muted);font-size:12px">Aucune facture</div>';
            return;
        }

        result.data.forEach(facture => {
            const statusBadge = {
                'EN_ATTENTE': 'badge-danger',
                'PARTIELLE': 'badge-warn',
                'SOLDEE': 'badge-success'
            }[facture.Statut] || 'badge-info';

            const row = document.createElement('div');
            row.className = 'mini-row';
            row.style.cursor = 'pointer';
            row.onclick = () => window.location = `<?= BASE_URL ?>factures/${facture.ID_Facture}`;
            
            row.innerHTML = `
                <span style="font-weight:600;color:var(--bl)">${facture.Reference}</span>
                <span style="color:var(--muted);font-size:11px">${facture.Nom_Client || 'Client'}</span>
                <span>${formatCurrency(facture.Montant_TTC)}</span>
                <span class="badge ${statusBadge}">
                    ${facture.Statut === 'EN_ATTENTE' ? 'En attente' : 
                      facture.Statut === 'PARTIELLE' ? 'Partielle' : 'Soldée'}
                </span>
            `;
            
            container.appendChild(row);
        });
    } catch (error) {
        console.error('Erreur chargement factures:', error);
    }
}

/**
 * Initialise les graphiques Chart.js
 */
function initCharts() {
    // Graphique CA mensuel
    const ctxCA = document.getElementById('chartCA');
    if (ctxCA) {
        new Chart(ctxCA, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                datasets: [{
                    label: 'CA (FCFA)',
                    data: [420000, 580000, 350000, 700000, 500000, 750000],
                    backgroundColor: 'var(--bl4)',
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => (v/1000)+'k' } }
                }
            }
        });
    }

    // Graphique Statut factures (Donut)
    const ctxStatut = document.getElementById('chartStatut');
    if (ctxStatut) {
        new Chart(ctxStatut, {
            type: 'doughnut',
            data: {
                labels: ['Soldées', 'Partielles', 'En attente'],
                datasets: [{
                    data: [10, 5, 3],
                    backgroundColor: ['var(--bl)', 'var(--or)', 'var(--danger)'],
                    borderColor: 'white',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    // Graphique Flux financier
    const ctxFlow = document.getElementById('chartFlow');
    if (ctxFlow) {
        new Chart(ctxFlow, {
            type: 'bar',
            data: {
                labels: ['Enc.', 'Dép.', 'Enc.', 'Dép.', 'Enc.', 'Dép.'],
                datasets: [{
                    data: [50, 30, 62, 22, 75, 40],
                    backgroundColor: ['var(--bl)', 'var(--or4)', 'var(--bl)', 'var(--or4)', 'var(--or)', 'var(--bl)']
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } }
            }
        });
    }
}