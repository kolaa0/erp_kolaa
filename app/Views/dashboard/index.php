<?php
$pageTitle = 'Tableau de bord';
$currentPage = 'dashboard';
$content = 'app/Views/dashboard/dashboard.php';
ob_start();
?>

<div class="kpi-row">
    <div class="kpi">
        <div class="kpi-label">Clients actifs</div>
        <div class="kpi-value"><?= $stats['clients_actifs'] ?? 0 ?></div>
        <div class="kpi-sub"><span class="up">↑</span> Actifs</div>
    </div>
    <div class="kpi orange">
        <div class="kpi-label">CA mensuel (FCFA)</div>
        <div class="kpi-value" style="font-size:17px"><?= number_format($stats['factures']['ca_total'] ?? 0, 0, ',', ' ') ?></div>
        <div class="kpi-sub">Total facturé</div>
    </div>
    <div class="kpi green">
        <div class="kpi-label">Encaissé (FCFA)</div>
        <div class="kpi-value" style="font-size:17px"><?= number_format($stats['factures']['encaisse'] ?? 0, 0, ',', ' ') ?></div>
        <div class="kpi-sub"><?= ($stats['factures']['ca_total'] > 0) ? round(($stats['factures']['encaisse'] / $stats['factures']['ca_total'] * 100)) : 0 ?>% collecté</div>
    </div>
    <div class="kpi red">
        <div class="kpi-label">Impayés (FCFA)</div>
        <div class="kpi-value" style="font-size:17px"><?= number_format($stats['factures']['solde_restant'] ?? 0, 0, ',', ' ') ?></div>
        <div class="kpi-sub"><?= $stats['factures_en_retard'] ?? 0 ?> factures en retard</div>
    </div>
</div>

<div class="grid2">
    <div class="card">
        <div class="card-head">
            <span class="card-title">Chiffre d'affaires mensuel</span>
            <span class="badge badge-info">2026</span>
        </div>
        <canvas id="chartCA" height="80"></canvas>
    </div>
    <div class="card">
        <div class="card-head">
            <span class="card-title">Statut des factures</span>
        </div>
        <canvas id="chartStatut" height="80"></canvas>
    </div>
</div>

<div class="grid2">
    <div class="card">
        <div class="card-head">
            <span class="card-title">Dernières factures</span>
            <span onclick="window.location='<?= BASE_URL ?>factures'" style="font-size:11.5px;color:var(--bl2);cursor:pointer">Voir tout →</span>
        </div>
        <div class="mini-list" id="latest-invoices">
            <!-- Chargé en AJAX -->
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <span class="card-title">Encaissements / Dépenses</span>
        </div>
        <canvas id="chartFlow" height="80"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= BASE_URL ?>js/dashboard.js"></script>

<?php
$content = ob_get_clean();
include ROOT_PATH . 'app/Views/layouts/main.php';
?>