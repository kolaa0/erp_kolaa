<?php
/**
 * Model Proforma - Mise à jour complète
 */

class Proforma extends Model {
    protected $table = 'PROFORMAS';
    protected $fillable = [
        'Reference',
        'Date_Emission',
        'Date_Validite',
        'ID_Client',
        'Objet',
        'Taux_TVA',
        'Montant_HT',
        'Montant_TVA',
        'Montant_TTC',
        'Statut',
        'ID_Facture_Liee',
        'ID_User',
        'Observations'
    ];

    /**
     * Génère la référence automatique
     */
    public function genererReference() {
        $annee = date('y');
        
        $query = "SELECT Numero_Courant FROM SEQUENCES 
                  WHERE Type_Document = 'PROFORMA' AND Annee = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$annee]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $numero = ($result['Numero_Courant'] ?? 0) + 1;

        $queryUpdate = "UPDATE SEQUENCES 
                       SET Numero_Courant = ? 
                       WHERE Type_Document = 'PROFORMA' AND Annee = ?";
        $stmtUpdate = $this->db->prepare($queryUpdate);
        $stmtUpdate->execute([$numero, $annee]);

        return "P-{$annee}-" . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Récupère les lignes d'un proforma
     */
    public function getLignes($idProforma) {
        $query = "SELECT * FROM LIGNES_PROFORMA WHERE ID_Proforma = ? ORDER BY Ordre ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$idProforma]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche multicritère
     */
    public function rechercher($terme) {
        $query = "SELECT p.*, c.Nom_Client FROM PROFORMAS p
                  LEFT JOIN CLIENTS c ON p.ID_Client = c.ID_Client
                  WHERE p.Reference LIKE ? OR c.Nom_Client LIKE ? OR p.Objet LIKE ?
                  ORDER BY p.Date_Creation DESC";
        
        $terme = "%{$terme}%";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$terme, $terme, $terme]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les proformas avec infos client
     */
    public function all() {
        $query = "SELECT p.*, c.Nom_Client FROM PROFORMAS p
                  LEFT JOIN CLIENTS c ON p.ID_Client = c.ID_Client
                  ORDER BY p.Date_Creation DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>