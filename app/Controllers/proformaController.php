<?php
/**
 * ProformaController - Gestion des proformas (devis)
 */

class ProformaController extends BaseController {

    /**
     * Liste tous les proformas
     */
    public function index() {
        $proforma = new Proforma();
        $page = intval($this->getQuery('page', 1));
        $search = $this->getQuery('search', '');

        if (!empty($search)) {
            $proformas = $proforma->rechercher($search);
            $data = [
                'proformas' => $proformas,
                'search' => $search,
                'user' => $this->user
            ];
        } else {
            $pagination = $proforma->paginate($page, 20);
            $data = [
                'proformas' => $pagination['data'],
                'pagination' => $pagination,
                'user' => $this->user
            ];
        }

        require_once ROOT_PATH . 'app/Views/proformas/index.php';
    }

    /**
     * Crée un nouveau proforma (JSON)
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $data = $this->getJsonData();

        // Valider les données
        if (empty($data['ID_Client']) || empty($data['Objet'])) {
            $this->sendError('Client et objet requis', 400);
        }

        // Générer la référence automatique
        $proforma = new Proforma();
        $reference = $proforma->genererReference();

        // Ajouter les données requises
        $data['Reference'] = $reference;
        $data['Date_Emission'] = $data['Date_Emission'] ?? date('Y-m-d');
        $data['Date_Creation'] = date('Y-m-d H:i:s');
        $data['ID_User'] = $this->user['ID_User'];
        $data['Statut'] = 'EN_ATTENTE';

        // Créer le proforma
        $id = $proforma->create($data);

        if ($id) {
            // Enregistrer les lignes
            if (isset($data['lignes']) && is_array($data['lignes'])) {
                foreach ($data['lignes'] as $ligne) {
                    $ligne['ID_Proforma'] = $id;
                    $this->db->insert('LIGNES_PROFORMA', $ligne);
                }
            }

            // Recalculer les totaux
            $this->updateProformaTotal($id);

            $this->log("Proforma créé : {$reference}", 'INFO');
            $this->success('Proforma créé avec succès', ['proforma_id' => $id, 'reference' => $reference], 201);
        } else {
            $this->sendError('Erreur lors de la création du proforma', 500);
        }
    }

    /**
     * Affiche les détails d'un proforma
     */
    public function show($id) {
        $proforma = new Proforma();
        $proformaData = $proforma->findById($id);

        if (!$proformaData) {
            $this->sendError('Proforma non trouvé', 404);
        }

        // Récupérer les lignes
        $lignes = $proforma->getLignes($id);

        // Récupérer les informations du client
        $client = new Client();
        $clientData = $client->findById($proformaData['ID_Client']);

        // Paramètres entreprise
        $parametre = new ParametreEntreprise();
        $parametreData = $parametre->getParametres();

        $data = [
            'proforma' => $proformaData,
            'lignes' => $lignes,
            'client' => $clientData,
            'parametres' => $parametreData,
            'user' => $this->user
        ];

        require_once ROOT_PATH . 'app/Views/proformas/show.php';
    }

    /**
     * Modifie un proforma (JSON)
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $data = $this->getJsonData();
        $proforma = new Proforma();

        if (!$proforma->findById($id)) {
            $this->sendError('Proforma non trouvé', 404);
        }

        if ($proforma->update($id, $data)) {
            $this->updateProformaTotal($id);
            $this->log("Proforma modifié : ID {$id}", 'INFO');
            $this->success('Proforma modifié avec succès');
        } else {
            $this->sendError('Erreur lors de la modification', 500);
        }
    }

    /**
     * Supprime un proforma (soft delete)
     */
    public function delete($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $proforma = new Proforma();
        if (!$proforma->findById($id)) {
            $this->sendError('Proforma non trouvé', 404);
        }

        if ($proforma->update($id, ['Statut' => 'REFUSE'])) {
            $this->log("Proforma supprimé : ID {$id}", 'INFO');
            $this->success('Proforma supprimé avec succès');
        } else {
            $this->sendError('Erreur lors de la suppression', 500);
        }
    }

    /**
     * Convertit un proforma en facture
     */
    public function convert($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $proforma = new Proforma();
        $proformaData = $proforma->findById($id);

        if (!$proformaData) {
            $this->sendError('Proforma non trouvé', 404);
        }

        if ($proformaData['Statut'] !== 'EN_ATTENTE' && $proformaData['Statut'] !== 'ACCEPTE') {
            $this->sendError('Seul un proforma accepté peut être converti', 400);
        }

        // Créer la facture
        $facture = new Facture();
        $reference = $facture->genererReference();

        $factureData = [
            'Reference' => $reference,
            'Date_Emission' => date('Y-m-d'),
            'Date_Echeance' => date('Y-m-d', strtotime('+30 days')),
            'ID_Client' => $proformaData['ID_Client'],
            'ID_Proforma' => $id,
            'Objet' => $proformaData['Objet'],
            'Taux_TVA' => $proformaData['Taux_TVA'],
            'Montant_HT' => $proformaData['Montant_HT'],
            'Montant_TVA' => $proformaData['Montant_TVA'],
            'Montant_TTC' => $proformaData['Montant_TTC'],
            'Montant_Paye' => 0,
            'Statut' => 'EN_ATTENTE',
            'ID_User' => $this->user['ID_User'],
            'Date_Creation' => date('Y-m-d H:i:s')
        ];

        $factureId = $facture->create($factureData);

        if ($factureId) {
            // Copier les lignes du proforma vers la facture
            $lignes = $proforma->getLignes($id);
            foreach ($lignes as $ligne) {
                unset($ligne['ID_Ligne']);
                unset($ligne['ID_Proforma']);
                $ligne['ID_Facture'] = $factureId;
                $this->db->insert('LIGNES_FACTURE', $ligne);
            }

            // Mettre à jour le proforma
            $proforma->update($id, ['Statut' => 'CONVERTI', 'ID_Facture_Liee' => $factureId]);

            $this->log("Proforma converti en facture : {$reference}", 'INFO');
            $this->success('Facture créée depuis le proforma', ['facture_id' => $factureId, 'reference' => $reference], 201);
        } else {
            $this->sendError('Erreur lors de la création de la facture', 500);
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

        $proforma = new Proforma();
        $query = "SELECT p.*, c.Nom_Client FROM PROFORMAS p
                  LEFT JOIN CLIENTS c ON p.ID_Client = c.ID_Client
                  WHERE p.Reference LIKE ? OR c.Nom_Client LIKE ? OR p.Objet LIKE ?
                  ORDER BY p.Date_Creation DESC";
        
        $terme = "%{$terme}%";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$terme, $terme, $terme]);
        $proformas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->success('Résultats de recherche', $proformas);
    }

    /**
     * Recalcule les totaux d'un proforma
     */
    private function updateProformaTotal($id) {
        $query = "SELECT 
                    SUM(Total_Ligne) as montant_ht
                  FROM LIGNES_PROFORMA
                  WHERE ID_Proforma = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $proformaData = $this->db->prepare("SELECT Taux_TVA FROM PROFORMAS WHERE ID_Proforma = ?")->execute([$id]);
        $proforma = $proformaData->fetch(PDO::FETCH_ASSOC);

        $montantHT = $result['montant_ht'] ?? 0;
        $tauxTVA = $proforma['Taux_TVA'] ?? 0;
        $montantTVA = $montantHT * ($tauxTVA / 100);
        $montantTTC = $montantHT + $montantTVA;

        $updateQuery = "UPDATE PROFORMAS 
                       SET Montant_HT = ?, Montant_TVA = ?, Montant_TTC = ?
                       WHERE ID_Proforma = ?";
        
        $stmt = $this->db->prepare($updateQuery);
        $stmt->execute([$montantHT, $montantTVA, $montantTTC, $id]);
    }
}

?>