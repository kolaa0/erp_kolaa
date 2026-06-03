<?php
/**
 * api.php - Routes API
 * AUTHENTIFICATION DÉSACTIVÉE TEMPORAIREMENT
 */

class ApiRouter {
    private $db;
    private $method;
    private $endpoint;
    private $params;
    private $user;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->method = $_SERVER['REQUEST_METHOD'];
        
        // ===== CRÉER UN UTILISATEUR DE TEST =====
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 1;
            $_SESSION['user_email'] = 'admin@kola.com';
            $_SESSION['user_role'] = 'ADMIN';
            $_SESSION['user_name'] = 'Admin Test';
        }
        
        $this->user = $_SESSION['user_id'] ?? null;
        $this->parseUrl();
    }

    /**
     * Parse l'URL
     */
    private function parseUrl() {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $url = str_replace('/api/', '', $url);
        $url = trim($url, '/');

        $parts = explode('/', $url);
        $this->endpoint = $parts[0] ?? '';
        $this->params = array_slice($parts, 1);
    }

    /**
     * Route les requêtes
     */
    public function route() {
        // Les routes publiques (login)
        if ($this->endpoint === 'login') {
            $controller = new AuthController();
            return $controller->login();
        }

        // Pour toutes les autres routes, créer un utilisateur de test
        if (!$this->user) {
            $_SESSION['user_id'] = 1;
            $_SESSION['user_email'] = 'admin@kola.com';
            $_SESSION['user_role'] = 'ADMIN';
            $_SESSION['user_name'] = 'Admin Test';
            $this->user = 1;
        }

        // ===== ROUTES CLIENTS =====
        if ($this->endpoint === 'clients') {
            $controller = new ClientController();
            
            if ($this->method === 'GET') {
                if (empty($this->params)) {
                    return $controller->index();
                }
                elseif ($this->params[0] === 'search') {
                    return $controller->search();
                }
                elseif (isset($this->params[0]) && is_numeric($this->params[0])) {
                    return $controller->show($this->params[0]);
                }
            }
            elseif ($this->method === 'POST') {
                if (isset($this->params[0]) && $this->params[0] === 'create') {
                    return $controller->store();
                }
            }
            elseif ($this->method === 'PUT' && isset($this->params[0]) && is_numeric($this->params[0])) {
                return $controller->update($this->params[0]);
            }
            elseif ($this->method === 'DELETE' && isset($this->params[0]) && is_numeric($this->params[0])) {
                return $controller->delete($this->params[0]);
            }
        }

        // ===== ROUTES PROFORMAS =====
        if ($this->endpoint === 'proformas') {
            $controller = new ProformaController();
            
            if ($this->method === 'GET') {
                if (empty($this->params)) {
                    return $controller->index();
                }
                elseif ($this->params[0] === 'search') {
                    return $controller->search();
                }
                elseif (isset($this->params[0]) && is_numeric($this->params[0])) {
                    return $controller->show($this->params[0]);
                }
            }
            elseif ($this->method === 'POST') {
                if (isset($this->params[0]) && $this->params[0] === 'create') {
                    return $controller->store();
                }
                elseif (isset($this->params[0]) && isset($this->params[1]) && $this->params[1] === 'convert') {
                    return $controller->convert($this->params[0]);
                }
            }
            elseif ($this->method === 'PUT' && isset($this->params[0]) && is_numeric($this->params[0])) {
                return $controller->update($this->params[0]);
            }
            elseif ($this->method === 'DELETE' && isset($this->params[0]) && is_numeric($this->params[0])) {
                return $controller->delete($this->params[0]);
            }
        }

        // ===== ROUTES FACTURES =====
        if ($this->endpoint === 'factures') {
            $controller = new FactureController();
            
            if ($this->method === 'GET') {
                if (empty($this->params)) {
                    return $controller->index();
                }
                elseif ($this->params[0] === 'search') {
                    return $controller->search();
                }
                elseif ($this->params[0] === 'stats') {
                    return $controller->stats();
                }
                elseif ($this->params[0] === 'en-retard') {
                    return $controller->enRetard();
                }
                elseif (isset($this->params[0]) && isset($this->params[1]) && $this->params[1] === 'pdf') {
                    return $controller->generatePDF($this->params[0]);
                }
                elseif (isset($this->params[0]) && is_numeric($this->params[0])) {
                    return $controller->show($this->params[0]);
                }
            }
            elseif ($this->method === 'POST') {
                if (isset($this->params[0]) && $this->params[0] === 'create') {
                    return $controller->store();
                }
                elseif (isset($this->params[0]) && isset($this->params[1]) && $this->params[1] === 'cancel') {
                    return $controller->cancel($this->params[0]);
                }
            }
            elseif ($this->method === 'PUT' && isset($this->params[0]) && is_numeric($this->params[0])) {
                return $controller->update($this->params[0]);
            }
        }

        // ===== ROUTES PAIEMENTS =====
        if ($this->endpoint === 'paiements') {
            $controller = new PaiementController();
            
            if ($this->method === 'GET' && empty($this->params)) {
                return $controller->index();
            }
            elseif ($this->method === 'POST' && isset($this->params[0]) && $this->params[0] === 'create') {
                return $controller->store();
            }
            elseif ($this->method === 'DELETE' && isset($this->params[0]) && is_numeric($this->params[0])) {
                return $controller->delete($this->params[0]);
            }
        }

        // ===== ROUTES TRÉSORERIE =====
        if ($this->endpoint === 'tresorerie') {
            $controller = new TresorieController();
            
            if ($this->method === 'GET') {
                if (empty($this->params)) {
                    return $controller->stats();
                }
                elseif ($this->params[0] === 'paiements-mode') {
                    return $controller->paiementsParMode();
                }
                elseif ($this->params[0] === 'depenses') {
                    return $controller->depensesMois();
                }
                elseif ($this->params[0] === 'depenses-categorie') {
                    return $controller->depensesParCategorie();
                }
            }
            elseif ($this->method === 'POST' && isset($this->params[0]) && $this->params[0] === 'depenses') {
                return $controller->addDepense();
            }
        }

        // ===== ROUTES UTILISATEURS =====
        if ($this->endpoint === 'utilisateurs') {
            $controller = new UtilisateurController();
            
            if ($this->method === 'GET') {
                if (empty($this->params)) {
                    return $controller->index();
                }
                elseif (isset($this->params[0]) && is_numeric($this->params[0])) {
                    return $controller->show($this->params[0]);
                }
            }
            elseif ($this->method === 'POST') {
                if (isset($this->params[0]) && $this->params[0] === 'create') {
                    return $controller->store();
                }
            }
            elseif ($this->method === 'PUT' && isset($this->params[0]) && is_numeric($this->params[0])) {
                return $controller->update($this->params[0]);
            }
        }

        // ===== ROUTES DASHBOARD =====
        if ($this->endpoint === 'dashboard') {
            $controller = new DashboardController();
            
            if ($this->method === 'GET') {
                if (isset($this->params[0]) && $this->params[0] === 'stats') {
                    return $controller->getStats();
                }
                elseif (isset($this->params[0]) && $this->params[0] === 'graphiques') {
                    return $controller->getGraphiques();
                }
            }
        }

        // Route non trouvée
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint non trouvé: ' . $this->endpoint
        ]);
    }
}

// Instancier et exécuter le routeur
$apiRouter = new ApiRouter();
$apiRouter->route();

?>