<?php
/**
 * Point d'entrée unique de l'application
 * AUTHENTIFICATION DÉSACTIVÉE TEMPORAIREMENT
 */

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger la configuration
require_once __DIR__ . '/../config/init.php';

// ===== CHARGER EXPLICITEMENT BaseController =====
require_once ROOT_PATH . 'app/Controllers/BaseController.php';

// ===== CRÉER UNE SESSION DE TEST (sans passer par login) =====
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_email'] = 'admin@kola.com';
    $_SESSION['user_role'] = 'ADMIN';
    $_SESSION['user_name'] = 'Admin Test';
    
    error_log("✅ Session de test créée");
}

// Récupérer l'utilisateur en session (ou simulé)
$user = [
    'ID_User' => $_SESSION['user_id'],
    'Email' => $_SESSION['user_email'] ?? 'test@test.com',
    'Role' => $_SESSION['user_role'] ?? 'ADMIN',
    'Nom_Complet' => $_SESSION['user_name'] ?? 'Test User',
    'Actif' => 1
];

// Récupérer l'URI demandée
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// ===== GESTION DES ROUTES API =====
if (strpos($request_uri, '/api/') !== false) {
    require_once ROOT_PATH . 'app/Routes/api.php';
    exit;
}

// ===== GESTION DES PAGES WEB =====

// Enlever le base path de l'URL
$base_path = dirname($_SERVER['SCRIPT_NAME']);
$uri = substr($request_uri, strlen($base_path));
$uri = trim($uri, '/');

// ===== ROUTES PUBLIQUES =====
if ($uri === 'login' || $uri === '') {
    header('Location: ' . BASE_URL . 'public/dashboard');
    exit;
}

if ($uri === 'logout') {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'public/login');
    exit;
}

// ===== ROUTES PROTÉGÉES =====

// Déterminer le contrôleur à partir de l'URI
$parts = explode('/', $uri);
$page = $parts[0] ?? 'dashboard';
$params = array_slice($parts, 1);

// Mapper les pages aux contrôleurs
$pageToController = [
    'dashboard' => 'Dashboard',
    'clients' => 'Client',
    'proformas' => 'Proforma',
    'factures' => 'Facture',
    'paiements' => 'Paiement',
    'tresorerie' => 'Tresorerie',
    'utilisateurs' => 'Utilisateur',
    'parametres' => 'Parametre',
    'catalogue' => 'Service'
];

// Obtenir le nom du contrôleur
$controllerName = $pageToController[$page] ?? ucfirst($page);
$controllerClass = $controllerName . 'Controller';
$controllerFile = ROOT_PATH . 'app/Controllers/' . $controllerClass . '.php';

// Vérifier si le contrôleur existe
if (!file_exists($controllerFile)) {
    $controllerClass = 'DashboardController';
    $controllerFile = ROOT_PATH . 'app/Controllers/DashboardController.php';
}

// Charger et instancier le contrôleur
if (file_exists($controllerFile)) {
    require_once $controllerFile;
} else {
    die("❌ Contrôleur non trouvé : {$controllerClass}");
}

try {
    $controller = new $controllerClass();
    
    // Déterminer quelle méthode appeler
    $action = !empty($params) ? $params[0] : 'index';
    $actionParams = array_slice($params, 1);
    
    // Vérifier si la méthode existe
    if (method_exists($controller, $action)) {
        call_user_func_array([$controller, $action], $actionParams);
    } else {
        $controller->index();
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo "<h1>❌ Erreur 500</h1>";
    echo "<p><strong>Message :</strong> " . $e->getMessage() . "</p>";
    if (DEBUG_MODE) {
        echo "<h3>Stack Trace :</h3>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}

?>