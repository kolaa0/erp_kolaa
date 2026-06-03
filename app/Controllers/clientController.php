<?php
/**
 * ClientController - Gestion des clients
 */

class ClientController extends BaseController {

    /**
     * Liste tous les clients
     */
    public function index() {
        $client = new Client();
        $page = intval($this->getQuery('page', 1));
        $search = $this->getQuery('search', '');

        if (!empty($search)) {
            $clients = $client->rechercher($search);
            $data = [
                'clients' => $clients,
                'search' => $search,
                'user' => $this->user
            ];
        } else {
            $pagination = $client->paginate($page, 20);
            $data = [
                'clients' => $pagination['data'],
                'pagination' => $pagination,
                'user' => $this->user
            ];
        }

        require_once ROOT_PATH . 'app/Views/clients/index.php';
    }

    /**
     * Affiche le formulaire de création
     */
    public function create() {
        $data = [
            'user' => $this->user,
            'csrf_token' => $this->generateCsrfToken()
        ];

        require_once ROOT_PATH . 'app/Views/clients/form.php';
    }

    /**
     * Enregistre un nouveau client (JSON)
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $data = $this->getJsonData();

        // Valider les données
        if (empty($data['Nom_Client']) || empty($data['Telephone'])) {
            $this->sendError('Nom du client et téléphone requis', 400);
        }

        // Créer le client
        $client = new Client();
        $id = $client->create($data);

        if ($id) {
            $this->log("Client créé : {$data['Nom_Client']}", 'INFO');
            $this->success('Client créé avec succès', ['client_id' => $id], 201);
        } else {
            $this->sendError('Erreur lors de la création du client', 500);
        }
    }

    /**
     * Affiche les détails d'un client
     */
    public function show($id) {
        $client = new Client();
        $clientData = $client->findById($id);

        if (!$clientData) {
            $this->sendError('Client non trouvé', 404);
        }

        // Récupérer les statistiques du client
        $stats = $client->getStatistiques($id);

        $data = [
            'client' => $clientData,
            'stats' => $stats,
            'user' => $this->user
        ];

        require_once ROOT_PATH . 'app/Views/clients/show.php';
    }

    /**
     * Affiche le formulaire de modification
     */
    public function edit($id) {
        $client = new Client();
        $clientData = $client->findById($id);

        if (!$clientData) {
            $this->sendError('Client non trouvé', 404);
        }

        $data = [
            'client' => $clientData,
            'user' => $this->user,
            'csrf_token' => $this->generateCsrfToken()
        ];

        require_once ROOT_PATH . 'app/Views/clients/form.php';
    }

    /**
     * Met à jour un client (JSON)
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $data = $this->getJsonData();

        $client = new Client();
        if (!$client->findById($id)) {
            $this->sendError('Client non trouvé', 404);
        }

        if ($client->update($id, $data)) {
            $this->log("Client modifié : ID {$id}", 'INFO');
            $this->success('Client modifié avec succès');
        } else {
            $this->sendError('Erreur lors de la modification du client', 500);
        }
    }

    /**
     * Supprime un client (soft delete)
     */
    public function delete($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $client = new Client();
        if (!$client->findById($id)) {
            $this->sendError('Client non trouvé', 404);
        }

        if ($client->update($id, ['Actif' => 0])) {
            $this->log("Client désactivé : ID {$id}", 'INFO');
            $this->success('Client désactivé avec succès');
        } else {
            $this->sendError('Erreur lors de la suppression du client', 500);
        }
    }

    /**
     * Recherche multicritère (JSON)
     */
    public function search() {
        $terme = $this->getQuery('q', '');

        if (empty($terme)) {
            $this->sendError('Terme de recherche requis', 400);
        }

        $client = new Client();
        $clients = $client->rechercher($terme);

        $this->success('Résultats de recherche', $clients);
    }
}

?>