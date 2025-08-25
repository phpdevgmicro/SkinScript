<?php
/**
 * Database Configuration
 * MySQL connection setup for skincare formulation app
 */

class Database {
    private $host = 'srv637.hstgr.io';
    private $username = 'u742355347_skingpt';
    private $password = 'Arona1@1@1@1';
    private $database = 'u742355347_skingpt';
    private $connection;

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        $this->connection = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->database . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->connection;
    }
}
?>