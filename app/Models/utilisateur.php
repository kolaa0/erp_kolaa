<?php
class Utilisateur extends Model {
    protected $table = 'UTILISATEURS';
    protected $fillable = ['Nom_Complet', 'Email', 'Mot_De_Passe', 'Role', 'Actif'];
    public function findByEmail($email) { return $this->findOne('Email', $email); }
}
?>
