<?php
/**
 * Model Client
 */

class Client extends Model {
    protected $table = 'CLIENTS';
    protected $fillable = [
        'Nom_Client',
        'Type_Client',
        'NIF',
        'RCCM',
        'Adresse',
        'Telephone',
        'Email',
        'Personne_Contact',
        'Actif',
        'Observation'
    ];

    /**
     * Retourne les clients actifs uniquement
     */
    public function actifs() {
        return $this->where('Actif', '=', 1);
    }

    /**
     * Recherche multicritère
     */
    public function rechercher($terme) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE Nom_Client LIKE ? 
                  OR Telephone LIKE ? 
                  OR Email LIKE ? 
                  OR NIF LIKE ?
                  OR RCCM LIKE ?
                  ORDER BY Nom_Client ASC";
        
        $terme = "%{$terme}%";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$terme, $terme, $terme, $terme, $terme]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Statistiques client
     */
    public function getStatistiques($idClient) {
        $stats = [
            'total_factures' => 0,
            'ca_total' => 0,
            'paye' => 0,
            'solde_restant' => 0,
            'nombre_factures' => 0
        ];

        // Total factures et CA
        $query = "SELECT 
                    COUNT(*) as nb_factures,
                    COALESCE(SUM(Montant_TTC), 0) as ca_total,
                    COALESCE(SUM(Montant_Paye), 0) as paye,
                    COALESCE(SUM(Solde_Restant), 0) as solde_restant
                  FROM FACTURES 
                  WHERE ID_Client = ? AND Statut != 'ANNULEE'";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$idClient]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return array_merge($stats, $result ?: []);
    }
}

?>