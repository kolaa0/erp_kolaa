<?php
/**
 * Fichier de test de l'API
 * À ouvrir dans le navigateur : http://localhost/erp-kola/tests/test_api.php
 */

session_start();

// Configuration
define('BASE_PATH', dirname(dirname(__FILE__)) . '/');
require_once BASE_PATH . 'config/init.php';

// Vérifier si c'est une requête AJAX
$action = $_GET['action'] ?? 'home';
$response = [];

try {
    switch ($action) {
        // ===== AUTHENTIFICATION =====
        case 'test_login':
            $utilisateur = new Utilisateur();
            $user = $utilisateur->authentifier('admin@kola.com', 'password');
            
            if ($user) {
                $_SESSION['user_id'] = $user['ID_User'];
                $_SESSION['user_role'] = $user['Role'];
                $response = [
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'user' => $user
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Email ou mot de passe incorrect'
                ];
            }
            break;

        // ===== CLIENTS =====
        case 'list_clients':
            $client = new Client();
            $clients = $client->all();
            $response = [
                'success' => true,
                'message' => 'Clients récupérés',
                'data' => $clients
            ];
            break;

        case 'get_client':
            $id = $_GET['id'] ?? 1;
            $client = new Client();
            $clientData = $client->findById($id);
            
            if ($clientData) {
                $response = [
                    'success' => true,
                    'message' => 'Client récupéré',
                    'data' => $clientData
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Client non trouvé'
                ];
            }
            break;

        case 'create_client':
            $client = new Client();
            $id = $client->create([
                'Nom_Client' => 'Test Client ' . date('H:i:s'),
                'Type_Client' => 'Entreprise',
                'Adresse' => 'Bamako, Mali',
                'Telephone' => '+223 75 00 00 99',
                'Email' => 'test' . time() . '@example.com',
                'Actif' => 1
            ]);
            
            $response = [
                'success' => $id ? true : false,
                'message' => $id ? 'Client créé' : 'Erreur lors de la création',
                'client_id' => $id
            ];
            break;

        case 'search_clients':
            $terme = $_GET['term'] ?? '';
            $client = new Client();
            $clients = $client->rechercher($terme);
            
            $response = [
                'success' => true,
                'message' => 'Résultats de recherche',
                'data' => $clients
            ];
            break;

        // ===== FACTURES =====
        case 'list_factures':
            $facture = new Facture();
            $factures = $facture->all();
            $response = [
                'success' => true,
                'message' => 'Factures récupérées',
                'data' => $factures
            ];
            break;

        case 'facture_stats':
            $facture = new Facture();
            $stats = $facture->statistiques();
            $response = [
                'success' => true,
                'message' => 'Statistiques factures',
                'data' => $stats
            ];
            break;

        case 'facture_en_retard':
            $facture = new Facture();
            $factures = $facture->enRetard();
            $response = [
                'success' => true,
                'message' => 'Factures en retard',
                'data' => $factures
            ];
            break;

        // ===== UTILISATEURS =====
        case 'list_users':
            $utilisateur = new Utilisateur();
            $users = $utilisateur->all();
            
            // Masquer les mots de passe
            foreach ($users as &$user) {
                unset($user['Mot_De_Passe']);
            }
            
            $response = [
                'success' => true,
                'message' => 'Utilisateurs récupérés',
                'data' => $users
            ];
            break;

        case 'user_actifs':
            $utilisateur = new Utilisateur();
            $users = $utilisateur->actifs();
            
            foreach ($users as &$user) {
                unset($user['Mot_De_Passe']);
            }
            
            $response = [
                'success' => true,
                'message' => 'Utilisateurs actifs',
                'data' => $users
            ];
            break;

        // ===== DÉPENSES =====
        case 'list_depenses':
            $depense = new Depense();
            $depenses = $depense->all();
            $response = [
                'success' => true,
                'message' => 'Dépenses récupérées',
                'data' => $depenses
            ];
            break;

        case 'depense_mois':
            $depense = new Depense();
            $total = $depense->getTotalMois();
            $response = [
                'success' => true,
                'message' => 'Total dépenses du mois',
                'data' => ['total' => $total]
            ];
            break;

        case 'depense_categorie':
            $depense = new Depense();
            $data = $depense->parCategorie();
            $response = [
                'success' => true,
                'message' => 'Dépenses par catégorie',
                'data' => $data
            ];
            break;

        // ===== PAGE D'ACCUEIL =====
        default:
            $response = [
                'success' => true,
                'message' => 'Tests API disponibles',
                'tests' => [
                    'AUTHENTIFICATION' => [
                        '?action=test_login' => 'Teste la connexion'
                    ],
                    'CLIENTS' => [
                        '?action=list_clients' => 'Liste tous les clients',
                        '?action=get_client&id=1' => 'Récupère un client',
                        '?action=create_client' => 'Crée un client de test',
                        '?action=search_clients&term=test' => 'Recherche de clients'
                    ],
                    'FACTURES' => [
                        '?action=list_factures' => 'Liste toutes les factures',
                        '?action=facture_stats' => 'Statistiques factures',
                        '?action=facture_en_retard' => 'Factures en retard'
                    ],
                    'UTILISATEURS' => [
                        '?action=list_users' => 'Liste tous les utilisateurs',
                        '?action=user_actifs' => 'Utilisateurs actifs'
                    ],
                    'DÉPENSES' => [
                        '?action=list_depenses' => 'Liste toutes les dépenses',
                        '?action=depense_mois' => 'Total dépenses du mois',
                        '?action=depense_categorie' => 'Dépenses par catégorie'
                    ]
                ]
            ];
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ];
}

// Retourner la réponse JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

?>