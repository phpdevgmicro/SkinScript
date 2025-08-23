<?php
/**
 * Database Configuration
 * Secure connection to Supabase PostgreSQL database
 */

class Database {
    private $host = 'db.hqwevjatsohdciirmmdo.supabase.co';
    private $port = '5432';
    private $dbname = 'postgres';
    private $username = 'postgres';
    private $password = 'Command$$5';
    private $pdo = null;
    private $fallbackMode = false;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            // Try connection with SSL
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname};sslmode=require";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_TIMEOUT => 15
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            
            // Create table if it doesn't exist
            $this->createFormulationTable();
            
        } catch (PDOException $e) {
            error_log("Primary database connection failed: " . $e->getMessage());
            
            // Try fallback without SSL
            try {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
                $this->createFormulationTable();
                error_log("Connected to database without SSL");
            } catch (PDOException $e2) {
                error_log("Fallback database connection also failed: " . $e2->getMessage());
                $this->fallbackMode = true;
                error_log("Switching to fallback mode - form submissions will be logged locally");
            }
        }
    }
    
    private function createFormulationTable() {
        $sql = "CREATE TABLE IF NOT EXISTS skincare_formulations (
            id SERIAL PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            skin_concerns TEXT,
            skin_type TEXT[] NOT NULL,
            base_format VARCHAR(50) NOT NULL,
            key_actives TEXT[] NOT NULL,
            extracts TEXT[],
            boosters TEXT[],
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            user_agent TEXT,
            screen_resolution VARCHAR(50),
            form_version VARCHAR(10)
        )";
        
        $this->pdo->exec($sql);
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function testConnection() {
        if ($this->fallbackMode) {
            return false;
        }
        
        try {
            $this->pdo->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            error_log("Database test failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function isFallbackMode() {
        return $this->fallbackMode;
    }
}
?>