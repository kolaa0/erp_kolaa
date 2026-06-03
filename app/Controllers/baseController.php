<?php
/**
 * BaseController - Classe parent pour tous les contrôleurs
 */

class BaseController {
    protected $db;
    protected $user = null;
    protected $statusCode = 200;
    protected $response = [];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        // Vérifier l'authentification
        $this->checkAuth();
    }

    /**
     * Vérifie si l'utilisateur est authentifié
     */
    protected function checkAuth() {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        // Récupérer l'utilisateur en session
        $utilisateur = new Utilisateur();
        $this->user = $utilisateur->findById($_SESSION['user_id']);

        if (!$this->user || !$this->user['Actif']) {
            $this->destroySession();
            $this->redirect('/login');
        }
    }

    /**
     * Vérifie si l'utilisateur a le rôle ADMIN
     */
    protected function requireAdmin() {
        if ($this->user['Role'] !== 'ADMIN') {
            $this->sendError('Accès refusé. Droits administrateur requis.', 403);
            exit;
        }
    }

    /**
     * Retourne une réponse JSON
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Envoie une réponse de succès
     */
    protected function success($message = 'Succès', $data = null, $statusCode = 200) {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
        
        $this->json($response, $statusCode);
    }

    /**
     * Envoie une réponse d'erreur
     */
    protected function sendError($message = 'Erreur', $statusCode = 400, $errors = null) {
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ];
        
        $this->json($response, $statusCode);
    }

    /**
     * Valide un token CSRF
     */
    protected function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
            $this->sendError('Token CSRF invalide', 403);
        }
    }

    /**
     * Génère un token CSRF
     */
    protected function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Récupère les données JSON du body
     */
    protected function getJsonData() {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }

    /**
     * Récupère une valeur POST sécurisée
     */
    protected function getPost($key, $default = null) {
        return isset($_POST[$key]) ? htmlspecialchars($_POST[$key], ENT_QUOTES, 'UTF-8') : $default;
    }

    /**
     * Récupère une valeur GET sécurisée
     */
    protected function getQuery($key, $default = null) {
        return isset($_GET[$key]) ? htmlspecialchars($_GET[$key], ENT_QUOTES, 'UTF-8') : $default;
    }

    /**
     * Redirige vers une URL
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Détruit la session
     */
    protected function destroySession() {
        session_unset();
        session_destroy();
    }

    /**
     * Log les erreurs
     */
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