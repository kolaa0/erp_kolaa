<?php
/**
 * Configuration générale de l'application
 */

// URL de base de l'application
define('BASE_URL', 'http://localhost/erp-kola/public/');

// Dossier racine du projet
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');

// Dossier public
define('PUBLIC_PATH', ROOT_PATH . 'public/');

// Dossier uploads
define('UPLOAD_PATH', ROOT_PATH . 'public/uploads/');

// Titre de l'application
define('APP_NAME', 'ERP Kola');
define('APP_VERSION', '1.0.0');

// Fuseau horaire
date_default_timezone_set('Africa/Bamako');

// Mode de débogage
define('DEBUG_MODE', true); // Mettre à false en production

// Configuration des sessions (si pas déjà démarrée)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
}

// Durée de session (en secondes)
define('SESSION_TIMEOUT', 3600); // 1 heure

// Constantes métier
define('DEVISE', 'FCFA');
define('TAUX_TVA_DEFAUT', 18.00);
define('DELAI_PAIEMENT_JOURS', 30);

// Messages de statut
define('STATUT_PROFORMA', ['EN_ATTENTE', 'ACCEPTE', 'REFUSE', 'CONVERTI']);
define('STATUT_FACTURE', ['EN_ATTENTE', 'PARTIELLE', 'SOLDEE', 'ANNULEE']);
define('STATUT_LIVRAISON', ['EN_COURS', 'LIVRE', 'PARTIEL']);

// Modes de paiement
define('MODES_PAIEMENT', ['Especes', 'Cheque', 'Virement', 'Orange_Money', 'Moov_Money', 'Autre']);

?>