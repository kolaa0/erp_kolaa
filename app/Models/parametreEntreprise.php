<?php
/**
 * Model Paramètres Entreprise
 */

class ParametreEntreprise extends Model {
    protected $table = 'PARAMETRES_ENTREPRISE';
    protected $fillable = [
        'Nom_Entreprise',
        'Logo',
        'Sigle',
        'NIF',
        'RCCM',
        'Adresse',
        'Telephone_Principal',
        'Telephone_Secondaire',
        'Email',
        'Site_Web',
        'Coordonnee_Bancaire',
        'Moyen_Paiement_Mobile',
        'Mentions_Legales',
        'Signature'
    ];

    /**
     * Récupère les paramètres (il n'y en a qu'un)
     */
    public function getParametres() {
        $query = "SELECT * FROM {$this->table} LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour les paramètres
     */
    public function updateParametres($data) {
        $parametre = $this->getParametres();
        
        if ($parametre) {
            return $this->update($parametre['ID_Entreprise'], $data);
        } else {
            return $this->create($data);
        }
    }
}

?>