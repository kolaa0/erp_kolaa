<?php
/**
 * DashboardController - Tableau de bord principal
 */

class DashboardController extends BaseController {

    /**
     * Affiche le tableau de bord
     */
    public function index() {
        // Récupérer les statistiques
        $facture = new Facture();
        $client = new Client();
        $depense = new Depense();

        $stats = [
            'factures' => $facture->statistiques(),
            'clients_actifs' => $client->count('Actif = 1'),
            'factures_en_retard' => count($facture->enRetard()),
            'total_depenses_mois' => $this->getDepensesMois()
        ];

        // Préparer les données pour le rendu
        $data = [
            'user' => $this->user,
            'stats' => $stats,
            'csrf_token' => $this->generateCsrfToken()
        ];

        require_once ROOT_PATH . 'app/Views/dashboard/index.php';
    }

    /**
     * Retourne les dépenses du mois courant (JSON)
     */
    public function getDepensesMois() {
        $depense = new Depense();
        $query = "SELECT COALESCE(SUM(Montant), 0) as total 
                  FROM DEPENSES 
                  WHERE MONTH(Date_Depense) = MONTH(NOW()) 
                  AND YEAR(Date_Depense) = YEAR(NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Retourne les statistiques en JSON (pour AJAX)
     */
    public function getStats() {
        $facture = new Facture();
        $client = new Client();

        $stats = [
            'factures' => $facture->statistiques(),
            'clients_actifs' => $client->count('Actif = 1'),
            'factures_en_retard' => count($facture->enRetard()),
            'total_depenses_mois' => $this->getDepensesMois()
        ];

        $this->success('Statistiques récupérées', $stats);
    }

    /**
     * Récupère les données pour les graphiques (JSON)
     */
    public function getGraphiques() {
        $query = "SELECT 
                    DATE_FORMAT(Date_Emission, '%Y-%m') as mois,
                    SUM(Montant_TTC) as ca,
                    SUM(Montant_Paye) as encaisse
                  FROM FACTURES
                  WHERE Date_Emission >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  AND Statut != 'ANNULEE'
                  GROUP BY DATE_FORMAT(Date_Emission, '%Y-%m')
                  ORDER BY mois ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $donnees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->success('Données graphiques', $donnees);
    }
}

?>
