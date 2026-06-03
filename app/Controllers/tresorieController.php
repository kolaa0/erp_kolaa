<?php
/**
 * TresorieController - Gestion de la trésorerie
 */

class TresorieController extends BaseController {

    /**
     * Affiche le tableau de trésorerie
     */
    public function index() {
        $data = [
            'user' => $this->user,
            'stats' => $this->getTresorieStats()
        ];

        require_once ROOT_PATH . 'app/Views/tresorerie/index.php';
    }

    /**
     * Récupère les statistiques de trésorerie (JSON)
     */
    public function stats() {
        $stats = $this->getTresorieStats();
        $this->success('Statistiques trésorerie', $stats);
    }

    /**
     * Récupère les paiements par mode (JSON)
     */
    public function paiementsParMode() {
        $query = "SELECT 
                    Mode_Paiement,
                    COUNT(*) as nombre,
                    SUM(Montant) as total
                  FROM PAIEMENTS
                  WHERE MONTH(Date_Paiement) = MONTH(NOW())
                  AND YEAR(Date_Paiement) = YEAR(NOW())
                  GROUP BY Mode_Paiement
                  ORDER BY total DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->success('Paiements par mode', $result);
    }

    /**
     * Récupère les dépenses du mois (JSON)
     */
    public function depensesMois() {
        $depense = new Depense();
        $depenses = $depense->getMoisCourant();

        $this->success('Dépenses du mois', $depenses);
    }

    /**
     * Récupère les dépenses par catégorie (JSON)
     */
    public function depensesParCategorie() {
        $depense = new Depense();
        $result = $depense->parCategorie();

        $this->success('Dépenses par catégorie', $result);
    }

    /**
     * Enregistre une dépense (JSON)
     */
    public function addDepense() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $data = $this->getJsonData();

        // Valider les données
        if (empty($data['Libelle']) || empty($data['Montant'])) {
            $this->sendError('Libellé et montant requis', 400);
        }

        $data['ID_User'] = $this->user['ID_User'];
        $data['Date_Depense'] = $data['Date_Depense'] ?? date('Y-m-d H:i:s');
        $data['Date_Creation'] = date('Y-m-d H:i:s');

        $depense = new Depense();
        $id = $depense->create($data);

        if ($id) {
            $this->log("Dépense enregistrée : {$data['Libelle']}", 'INFO');
            $this->success('Dépense enregistrée avec succès', ['depense_id' => $id], 201);
        } else {
            $this->sendError('Erreur lors de l\'enregistrement', 500);
        }
    }

    /**
     * Récupère les données consolidées de trésorerie
     */
    private function getTresorieStats() {
        // Encaissements du mois
        $queryEncaisse = "SELECT 
                            COALESCE(SUM(Montant), 0) as total,
                            Mode_Paiement
                          FROM PAIEMENTS
                          WHERE MONTH(Date_Paiement) = MONTH(NOW())
                          AND YEAR(Date_Paiement) = YEAR(NOW())
                          GROUP BY Mode_Paiement";
        
        $stmt = $this->db->prepare($queryEncaisse);
        $stmt->execute();
        $encaissements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalEncaisse = array_sum(array_column($encaissements, 'total'));

        // Dépenses du mois
        $depense = new Depense();
        $totalDepense = $depense->getTotalMois();
        $depensesDetail = $depense->getMoisCourant();

        return [
            'encaisse' => $totalEncaisse,
            'depenses' => $totalDepense,
            'resultat_net' => $totalEncaisse - $totalDepense,
            'encaissements_mode' => $encaissements,
            'depenses_detail' => $depensesDetail,
            'paiements' => $this->getPaiementsRecents()
        ];
    }

    /**
     * Récupère les paiements récents
     */
    private function getPaiementsRecents() {
        $query = "SELECT 
                    p.*,
                    f.Reference,
                    c.Nom_Client
                  FROM PAIEMENTS p
                  LEFT JOIN FACTURES f ON p.ID_Facture = f.ID_Facture
                  LEFT JOIN CLIENTS c ON f.ID_Client = c.ID_Client
                  WHERE MONTH(p.Date_Paiement) = MONTH(NOW())
                  AND YEAR(p.Date_Paiement) = YEAR(NOW())
                  ORDER BY p.Date_Paiement DESC
                  LIMIT 20";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>