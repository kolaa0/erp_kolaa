<?php
/**
 * Classe Database - Gestion de la connexion PDO (Singleton)
 */

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $this->connect();
    }

    /**
     * Établit la connexion à la base de données
     */
    private function connect() {
        try {
            // Paramètres de connexion
            $host = 'localhost';
            $user = 'root';
            $pass = '';
            $dbname = 'erp_kola';
            $port = 3306;

            // DSN (Data Source Name)
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            // Créer la connexion PDO
            $this->pdo = new PDO($dsn, $user, $pass);
            
            // Configuration PDO
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            error_log("✅ Connexion BD établie avec succès");
            
        } catch (PDOException $e) {
            error_log("❌ Erreur de connexion BD : " . $e->getMessage());
            die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    /**
     * Récupère l'instance unique de la connexion
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retourne l'objet PDO
     */
    public function getConnection() {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }

    /**
     * Exécute une requête préparée
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("❌ Erreur SQL : " . $e->getMessage());
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                die("❌ Erreur SQL : " . $e->getMessage());
            } else {
                die("❌ Une erreur est survenue. Veuillez contacter l'administrateur.");
            }
        }
    }

    /**
     * Récupère une seule ligne
     */
    public function fetchOne($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetch();
    }

    /**
     * Récupère toutes les lignes
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll();
    }

    /**
     * Insère une ligne et retourne l'ID
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $this->execute($query, array_values($data));
        return $this->pdo->lastInsertId();
    }

    /**
     * Met à jour une ligne
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
        $query = "UPDATE $table SET $set WHERE $where";
        
        $params = array_merge(array_values($data), $whereParams);
        return $this->execute($query, $params);
    }

    /**
     * Supprime une ligne
     */
    public function delete($table, $where, $params = []) {
        $query = "DELETE FROM $table WHERE $where";
        return $this->execute($query, $params);
    }

    /**
     * Compte les lignes
     */
    public function count($table, $where = '', $params = []) {
        $query = "SELECT COUNT(*) as total FROM $table";
        if ($where) {
            $query .= " WHERE $where";
        }
        $result = $this->fetchOne($query, $params);
        return $result['total'] ?? 0;
    }
}

?>