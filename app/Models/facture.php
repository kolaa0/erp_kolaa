<?php
/**
 * Model Facture - Mise à jour complète
 */

class Facture extends Model {
    protected $table = 'FACTURES';
    protected $fillable = [
        'Reference',
        'Date_Emission',
        'Date_Echeance',
        'ID_Client',
        'ID_Proforma',
        'Objet',
        'Taux_TVA',
        'Montant_HT',
        'Montant_TVA',
        'Montant_TTC',
        'Montant_Paye',
        'Statut',
        'ID_User',
        'Observations'
    ];

    /**
     * Génère la référence automatique
     */
    public function genererReference() {
        $annee = date('y');
        
        $query = "SELECT Numero_Courant FROM SEQUENCES 
                  WHERE Type_Document = 'FACTURE' AND Annee = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$annee]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $numero = ($result['Numero_Courant'] ?? 0) + 1;

        $queryUpdate = "UPDATE SEQUENCES 
                       SET Numero_Courant = ? 
                       WHERE Type_Document = 'FACTURE' AND Annee = ?";
        $stmtUpdate = $this->db->prepare($queryUpdate);
        $stmtUpdate->execute([$numero, $annee]);

        return "F-{$annee}-" . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Récupère les factures impayées
     */
    public function impayees() {
        $query = "SELECT * FROM FACTURES 
                  WHERE Statut IN ('EN_ATTENTE', 'PARTIELLE') 
                  ORDER BY Date_Echeance ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les factures en retard
     */
    public function enRetard() {
        $query = "SELECT * FROM FACTURES 
                  WHERE Statut IN ('EN_ATTENTE', 'PARTIELLE') 
                  AND Date_Echeance < CURDATE()
                  ORDER BY Date_Echeance ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Statistiques financières
     */
    public function statistiques() {
        $query = "SELECT 
                    COALESCE(SUM(Montant_TTC), 0) as ca_total,
                    COALESCE(SUM(Montant_Paye), 0) as encaisse,
                    COALESCE(SUM(Solde_Restant), 0) as solde_restant,
                    COUNT(*) as nb_factures,
                    SUM(CASE WHEN Statut = 'SOLDEE' THEN 1 ELSE 0 END) as nb_soldees,
                    SUM(CASE WHEN Statut IN ('EN_ATTENTE', 'PARTIELLE') THEN 1 ELSE 0 END) as nb_impayees
                  FROM FACTURES
                  WHERE Statut != 'ANNULEE'";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les lignes d'une facture
     */
    public function getLignes($idFacture) {
        $query = "SELECT * FROM LIGNES_FACTURE WHERE ID_Facture = ? ORDER BY Ordre ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$idFacture]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche multicritère
     */
    public function rechercher($terme) {
        $query = "SELECT f.*, c.Nom_Client FROM FACTURES f
                  LEFT JOIN CLIENTS c ON f.ID_Client = c.ID_Client
                  WHERE f.Reference LIKE ? OR c.Nom_Client LIKE ? OR f.Objet LIKE ?
                  AND f.Statut != 'ANNULEE'
                  ORDER BY f.Date_Emission DESC";
        
        $terme = "%{$terme}%";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$terme, $terme, $terme]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les factures avec infos client
     */
    public function all() {
        $query = "SELECT f.*, c.Nom_Client FROM FACTURES f
                  LEFT JOIN CLIENTS c ON f.ID_Client = c.ID_Client
                  WHERE f.Statut != 'ANNULEE'
                  ORDER BY f.Date_Emission DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>