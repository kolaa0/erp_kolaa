<?php
/**
 * Layout principal
 * Navbar + Sidebar + Contenu principal
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — <?= $pageTitle ?? 'Accueil' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>

<!-- TOP BAR HEADER -->
<div style="background:var(--dark);height:52px;display:flex;align-items:center;padding:0 24px;justify-content:space-between">
  <span style="color:rgba(255,255,255,.5);font-size:12px">ERP <?= APP_NAME ?> · v<?= APP_VERSION ?></span>
</div>

<div class="app">
  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-mark">
        <div class="logo-icon">K</div>
        <div>
          <div class="logo-text">AgencePro ERP</div>
          <div class="logo-sub">v1.0 · 2026</div>
        </div>
      </div>
    </div>

    <div class="nav">
      <!-- Principal -->
      <div class="nav-section">Principal</div>
      <a href="<?= BASE_URL ?>dashboard" class="nav-item <?= ($currentPage === 'dashboard') ? 'active' : '' ?>">
        <i class="ti ti-home"></i> Dashboard
      </a>

      <!-- Commercial -->
      <div class="nav-section">Commercial</div>
      <a href="<?= BASE_URL ?>clients" class="nav-item <?= ($currentPage === 'clients') ? 'active' : '' ?>">
        <i class="ti ti-users"></i> Clients
      </a>
      <a href="<?= BASE_URL ?>proformas" class="nav-item <?= ($currentPage === 'proformas') ? 'active' : '' ?>">
        <i class="ti ti-file-text"></i> Proformas
      </a>
      <a href="<?= BASE_URL ?>factures" class="nav-item <?= ($currentPage === 'factures') ? 'active' : '' ?>">
        <i class="ti ti-receipt"></i> Factures
        <span class="nav-badge" id="factures-badge" style="display:none">0</span>
      </a>
      <a href="<?= BASE_URL ?>bons-livraison" class="nav-item">
        <i class="ti ti-truck"></i> Bons de livraison
      </a>

      <!-- Finances -->
      <div class="nav-section">Finances</div>
      <a href="<?= BASE_URL ?>paiements" class="nav-item">
        <i class="ti ti-credit-card"></i> Paiements
      </a>
      <a href="<?= BASE_URL ?>tresorerie" class="nav-item <?= ($currentPage === 'tresorerie') ? 'active' : '' ?>">
        <i class="ti ti-chart-bar"></i> Trésorerie
      </a>
      <a href="<?= BASE_URL ?>depenses" class="nav-item">
        <i class="ti ti-minus"></i> Dépenses
      </a>

      <!-- Paramètres (Admin only) -->
      <?php if ($user['Role'] === 'ADMIN'): ?>
      <div class="nav-section">Paramètres</div>
      <a href="<?= BASE_URL ?>parametres/entreprise" class="nav-item">
        <i class="ti ti-settings"></i> Entreprise
      </a>
      <a href="<?= BASE_URL ?>utilisateurs" class="nav-item">
        <i class="ti ti-users"></i> Utilisateurs
      </a>
      <a href="<?= BASE_URL ?>catalogue" class="nav-item">
        <i class="ti ti-list"></i> Catalogue
      </a>
      <?php endif; ?>
    </div>

    <!-- User Profile -->
    <div class="sidebar-user">
      <div class="user-av"><?= substr($user['Nom_Complet'], 0, 2) ?></div>
      <div>
        <div class="user-name"><?= $user['Nom_Complet'] ?></div>
        <div class="user-role"><?= $user['Role'] === 'ADMIN' ? 'Administrateur' : 'Employé' ?></div>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="main">
    <!-- TOPBAR -->
    <div class="topbar">
      <div id="topbar-title" class="topbar-title"><?= $pageTitle ?? 'Accueil' ?></div>
      <div class="topbar-right">
        <div class="search-bar">
          <i class="ti ti-search"></i>
          <input type="text" placeholder="Rechercher…" style="border:none;background:none;outline:none;width:100%;font-size:12.5px">
        </div>
        <button class="tb-btn">
          <i class="ti ti-bell"></i>
        </button>
        <a href="<?= BASE_URL ?>logout" class="tb-btn">
          <i class="ti ti-logout"></i> Déconnexion
        </a>
      </div>
    </div>

    <!-- PAGE CONTENT -->
    <div class="page active" style="display:block !important;">
      <?php include $content; ?>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>js/app.js"></script>
<?php if (isset($scripts)): ?>
  <?php foreach ($scripts as $script): ?>
    <script src="<?= BASE_URL ?>js/<?= $script ?>.js"></script>
  <?php endforeach; ?>
<?php endif; ?>

</body>
</html>