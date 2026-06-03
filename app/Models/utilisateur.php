<?php
/**
 * Model Utilisateur
 */

class Utilisateur extends Model {
    protected $table = 'UTILISATEURS';
    protected $fillable = [
        'Nom_Complet',
        'Email',
        'Mot_De_Passe',
        'Role',
        'Actif',
        'Derniere_Connexion'
    ];

    /**
     * Authentification utilisateur
     */
    public function authentifier($email, $password) {
        $user = $this->findOne('Email', $email);
        
        if (!$user) {
            return null;
        }

        // Vérifier le mot de passe haché
        if (!password_verify($password, $user['Mot_De_Passe'])) {
            return null;
        }

        if (!$user['Actif']) {
            return null;
        }

        // Mettre à jour la dernière connexion
        $this->update($user['ID_User'], [
            'Derniere_Connexion' => date('Y-m-d H:i:s')
        ]);

        return $user;
    }

    /**
     * Crée un utilisateur avec mot de passe haché
     */
    public function creerUtilisateur($data) {
        if (isset($data['Mot_De_Passe'])) {
            $data['Mot_De_Passe'] = password_hash($data['Mot_De_Passe'], PASSWORD_BCRYPT);
        }
        
        return $this->create($data);
    }

    /**
     * Retourne tous les utilisateurs actifs
     */
    public function actifs() {
        return $this->where('Actif', '=', 1);
    }

    /**
     * Vérifie si l'email existe
     */
    public function emailExiste($email, $exceptId = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE Email = ?";
        
        if ($exceptId) {
            $query .= " AND ID_User != ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$email, $exceptId]);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$email]);
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}

?>