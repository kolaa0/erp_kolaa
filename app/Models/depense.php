<?php
/**
 * Model Depense
 */

class Depense extends Model {
    protected $table = 'DEPENSES';
    protected $fillable = [
        'Date_Depense',
        'Libelle',
        'Categorie',
        'Montant',
        'Justificatif',
        'ID_User',
        'Observation'
    ];

    /**
     * Récupère les dépenses du mois courant
     */
    public function getMoisCourant() {
        $query = "SELECT * FROM {$this->table} 
                  WHERE MONTH(Date_Depense) = MONTH(NOW())
                  AND YEAR(Date_Depense) = YEAR(NOW())
                  ORDER BY Date_Depense DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le total des dépenses du mois
     */
    public function getTotalMois() {
        $query = "SELECT COALESCE(SUM(Montant), 0) as total 
                  FROM {$this->table}
                  WHERE MONTH(Date_Depense) = MONTH(NOW())
                  AND YEAR(Date_Depense) = YEAR(NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Récupère les dépenses par catégorie (pour graphiques)
     */
    public function parCategorie() {
        $query = "SELECT 
                    Categorie,
                    COALESCE(SUM(Montant), 0) as total,
                    COUNT(*) as nombre
                  FROM {$this->table}
                  WHERE Date_Depense >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                  GROUP BY Categorie
                  ORDER BY total DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>