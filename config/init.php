<?php
/**
 * Initialisation de l'application
 */

// Charger les fichiers de configuration
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Router.php';

// Auto-loading des classes
spl_autoload_register(function ($class) {
    // Charger BaseController en priorité
    if ($class === 'BaseController') {
        $file = ROOT_PATH . 'app/Controllers/BaseController.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // Dossier Models
    $modelFile = ROOT_PATH . 'app/Models/' . $class . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
        return;
    }

    // Dossier Controllers
    $controllerFile = ROOT_PATH . 'app/Controllers/' . $class . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return;
    }
});

?>