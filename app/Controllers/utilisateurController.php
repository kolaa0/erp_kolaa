<?php
/**
 * UtilisateurController - Gestion des utilisateurs
 */

class UtilisateurController extends BaseController {

    public function __construct() {
        parent::__construct();
        $this->requireAdmin(); // Réservé aux admins
    }

    /**
     * Liste tous les utilisateurs
     */
    public function index() {
        $utilisateur = new Utilisateur();
        $users = $utilisateur->all();

        $data = [
            'users' => $users,
            'user' => $this->user
        ];

        require_once ROOT_PATH . 'app/Views/utilisateurs/index.php';
    }

    /**
     * Crée un nouvel utilisateur (JSON)
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $data = $this->getJsonData();

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
            $this->log("Utilisateur créé : {$data['Email']}", 'INFO');
            $this->success('Utilisateur créé avec succès', ['user_id' => $id], 201);
        } else {
            $this->sendError('Erreur lors de la création de l\'utilisateur', 500);
        }
    }

    /**
     * Récupère les détails d'un utilisateur (JSON)
     */
    public function show($id) {
        $utilisateur = new Utilisateur();
        $user = $utilisateur->findById($id);

        if (!$user) {
            $this->sendError('Utilisateur non trouvé', 404);
        }

        // Masquer le mot de passe
        unset($user['Mot_De_Passe']);

        $this->success('Utilisateur récupéré', $user);
    }

    /**
     * Met à jour un utilisateur (JSON)
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $data = $this->getJsonData();
        $utilisateur = new Utilisateur();

        if (!$utilisateur->findById($id)) {
            $this->sendError('Utilisateur non trouvé', 404);
        }

        // Ne pas modifier le mot de passe ici (utiliser un endpoint dédié)
        unset($data['Mot_De_Passe']);

        if ($utilisateur->update($id, $data)) {
            $this->log("Utilisateur modifié : ID {$id}", 'INFO');
            $this->success('Utilisateur modifié avec succès');
        } else {
            $this->sendError('Erreur lors de la modification', 500);
        }
    }

    /**
     * Désactive un utilisateur
     */
    public function disable($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $utilisateur = new Utilisateur();
        if (!$utilisateur->findById($id)) {
            $this->sendError('Utilisateur non trouvé', 404);
        }

        if ($utilisateur->update($id, ['Actif' => 0])) {
            $this->log("Utilisateur désactivé : ID {$id}", 'INFO');
            $this->success('Utilisateur désactivé avec succès');
        } else {
            $this->sendError('Erreur lors de la désactivation', 500);
        }
    }
}

?>