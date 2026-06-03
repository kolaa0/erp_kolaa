<?php
/**
 * AuthController - Gestion de l'authentification
 */

class AuthController {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Affiche la page de login
     */
    public function loginForm() {
        $csrf_token = $this->generateCsrfToken();
        require_once ROOT_PATH . 'app/Views/auth/login.php';
    }

    /**
     * Traite la connexion
     */
   /**
 * Traite la connexion
 */
public function login() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->sendError('Méthode non autorisée', 405);
    }

    // Récupérer les données POST
    $email = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'] ?? '';

    // Validation basique
    if (empty($email) || empty($password)) {
        $this->sendError('Email et mot de passe requis', 400);
    }

    // Authentifier l'utilisateur
    $utilisateur = new Utilisateur();
    
    // Chercher l'utilisateur dans la BD
    $query = "SELECT * FROM UTILISATEURS WHERE Email = ?";
    $stmt = $this->db->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        error_log("❌ Utilisateur non trouvé : {$email}");
        $this->sendError('Email ou mot de passe incorrect', 401);
    }

    // Vérifier le mot de passe
    if (!password_verify($password, $user['Mot_De_Passe'])) {
        error_log("❌ Mot de passe incorrect pour : {$email}");
        $this->sendError('Email ou mot de passe incorrect', 401);
    }

    // Vérifier que le compte est actif
    if (!$user['Actif']) {
        error_log("❌ Compte inactif : {$email}");
        $this->sendError('Ce compte est désactivé', 401);
    }

    // Créer la session
    $_SESSION['user_id'] = $user['ID_User'];
    $_SESSION['user_email'] = $user['Email'];
    $_SESSION['user_role'] = $user['Role'];
    $_SESSION['user_name'] = $user['Nom_Complet'];

    error_log("✅ Connexion réussie : {$email}");

    // Retourner success
    $this->success('Connexion réussie', [
        'user_id' => $user['ID_User'],
        'redirect' => BASE_URL . 'public/dashboard'
    ]);
}

/**
 * Méthodes helper (au cas où BaseController ne les aurait pas)
 */
protected function sendError($message = 'Erreur', $statusCode = 400, $errors = null) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

protected function success($message = 'Succès', $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

    /**
     * Déconnexion
     */
    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function registerForm() {
        $csrf_token = $this->generateCsrfToken();
        require_once ROOT_PATH . 'app/Views/auth/register.php';
    }

    /**
     * Enregistre un nouvel utilisateur (Admin uniquement)
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        // Valider CSRF
        $token = $_POST['csrf_token'] ?? null;
        if (!$this->validateCsrfToken($token)) {
            $this->sendError('Token CSRF invalide', 403);
        }

        $data = [
            'Nom_Complet' => htmlspecialchars($_POST['nom_complet'] ?? '', ENT_QUOTES, 'UTF-8'),
            'Email' => htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'),
            'Mot_De_Passe' => $_POST['password'] ?? '',
            'Role' => 'EMPLOYE',
            'Actif' => 1
        ];

        // Valider les données
        if (empty($data['Nom_Complet']) || empty($data['Email']) || empty($data['Mot_De_Passe'])) {
            $this->sendError('Tous les champs sont requis', 400);
        }

        if (strlen($data['Mot_De_Passe']) < 6) {
            $this->sendError('Le mot de passe doit contenir au moins 6 caractères', 400);
        }

        // Vérifier que l'email n'existe pas
        $utilisateur = new Utilisateur();
        if ($utilisateur->emailExiste($data['Email'])) {
            $this->sendError('Cet email est déjà utilisé', 400);
        }

        // Créer l'utilisateur
        $id = $utilisateur->creerUtilisateur($data);

        if ($id) {
            $this->log("Nouvel utilisateur créé : {$data['Email']}", 'INFO');
            $this->success('Utilisateur créé avec succès', ['user_id' => $id], 201);
        } else {
            $this->sendError('Erreur lors de la création de l\'utilisateur', 500);
        }
    }

    // ===== Méthodes helper (déplacées de BaseController pour AuthController) =====

    protected function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
            return false;
        }
        return true;
    }

    protected function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function sendError($message = 'Erreur', $statusCode = 400, $errors = null) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    protected function success($message = 'Succès', $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    protected function log($message, $level = 'INFO') {
        if (DEBUG_MODE) {
            $logFile = ROOT_PATH . 'logs/app.log';
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0755, true);
            }
            
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
            
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }
    }
}

?>