<?php
/**
 * Classe Router - Gestion des routes URL
 */

class Router {
    private $routes = [];
    private $controller = null;
    private $action = null;
    private $params = [];

    /**
     * Enregistre une route
     */
    public function add($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Analyse l'URL et retourne le controller/action
     */
    public function dispatch($url, $method = 'GET') {
        $url = parse_url($url, PHP_URL_PATH);
        $url = str_replace(substr(BASE_URL, 0, -1), '', $url);
        $url = trim($url, '/');

        // Route par défaut : tableau de bord
        if (empty($url)) {
            $this->controller = 'Dashboard';
            $this->action = 'index';
            return;
        }

        // Essayer de matcher avec les routes enregistrées
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchRoute($route['path'], $url)) {
                $this->controller = $route['controller'];
                $this->action = $route['action'];
                return;
            }
        }

        // Fallback : essayer de déduire du URL (convention over configuration)
        $parts = explode('/', $url);
        $this->controller = ucfirst($parts[0] ?? 'Dashboard');
        $this->action = $parts[1] ?? 'index';
        $this->params = array_slice($parts, 2);
    }

    /**
     * Vérifie si une route correspond
     */
    private function matchRoute($routePath, $url) {
        $routeParts = explode('/', trim($routePath, '/'));
        $urlParts = explode('/', $url);

        if (count($routeParts) !== count($urlParts)) {
            return false;
        }

        foreach ($routeParts as $i => $part) {
            if (strpos($part, ':') === 0) {
                // Paramètre dynamique
                $paramName = substr($part, 1);
                $this->params[$paramName] = $urlParts[$i];
            } elseif ($part !== $urlParts[$i]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retourne le contrôleur
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * Retourne l'action
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Retourne les paramètres
     */
    public function getParams() {
        return $this->params;
    }
}

?>