<?php
/**
 * Classe Model - Base pour tous les modèles
 */

class Model {
    protected $db;
    protected $table;
    protected $fillable = [];
    protected $primaryKey = 'ID'; // Clé primaire par défaut

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Récupère tous les enregistrements
     */
    public function all() {
        $query = "SELECT * FROM {$this->table}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère par ID
     */
    public function findById($id) {
        // Déterminer le nom de la colonne ID
        $idColumn = $this->getIdColumn();
        
        $query = "SELECT * FROM {$this->table} WHERE {$idColumn} = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère avec filtres WHERE
     */
    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $query = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une seule ligne avec filtres
     */
    public function findOne($column, $value) {
        $query = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$value]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel enregistrement
     */
    public function create($data) {
        // Filtrer par fillable
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        if (empty($data)) {
            return false;
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute(array_values($data));
        
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Met à jour un enregistrement
     */
    public function update($id, $data) {
        // Filtrer par fillable
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        if (empty($data)) {
            return false;
        }

        // Déterminer le nom de la colonne ID
        $idColumn = $this->getIdColumn();

        $set = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        $query = "UPDATE {$this->table} SET {$set} WHERE {$idColumn} = ?";
        
        $params = array_merge(array_values($data), [$id]);
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($params);
    }

    /**
     * Supprime un enregistrement
     */
    public function delete($id) {
        $idColumn = $this->getIdColumn();
        $query = "DELETE FROM {$this->table} WHERE {$idColumn} = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Compte les enregistrements
     */
    public function count($where = null, $params = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($where) {
            $query .= " WHERE {$where}";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Pagination
     */
    public function paginate($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $total = $this->count();
        
        $query = "SELECT * FROM {$this->table} LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => ceil($total / $perPage)
        ];
    }

    /**
     * Retourne le nom de la colonne ID pour cette table
     */
    protected function getIdColumn() {
        // Mapper les tables à leurs colonnes ID
        $idColumns = [
            'UTILISATEURS' => 'ID_User',
            'CLIENTS' => 'ID_Client',
            'FACTURES' => 'ID_Facture',
            'PROFORMAS' => 'ID_Proforma',
            'DEPENSES' => 'ID_Depense',
            'PAIEMENTS' => 'ID_Paiement',
            'PARAMETRES_ENTREPRISE' => 'ID_Entreprise',
            'SERVICES' => 'ID_Service',
            'LIGNES_FACTURE' => 'ID_Ligne',
            'LIGNES_PROFORMA' => 'ID_Ligne',
            'SEQUENCES' => 'ID_Sequence'
        ];

        return $idColumns[$this->table] ?? 'ID_' . $this->table;
    }
}

?>