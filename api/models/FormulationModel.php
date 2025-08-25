<?php
/**
 * Formulation Model
 * Handles database operations for skincare formulations
 */

require_once __DIR__ . '/../config/database.php';

class FormulationModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Create formulations table if it doesn't exist
     */
    public function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS formulations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(50) NULL,
            skin_concerns TEXT NULL,
            skin_types JSON NOT NULL,
            base_format VARCHAR(50) NOT NULL,
            key_actives JSON NOT NULL,
            extracts JSON NOT NULL,
            boosters JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_customer_email (customer_email),
            INDEX idx_customer_name (customer_name),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $this->conn->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Error creating table: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save formulation data
     * @param array $data Formulation data
     * @return int|false Inserted ID or false on failure
     */
    public function saveFormulation($data) {
        $sql = "INSERT INTO formulations (
                    customer_name, customer_email, customer_phone, skin_concerns,
                    skin_types, base_format, key_actives, extracts, boosters
                ) VALUES (
                    :customer_name, :customer_email, :customer_phone, :skin_concerns,
                    :skin_types, :base_format, :key_actives, :extracts, :boosters
                )";

        try {
            $stmt = $this->conn->prepare($sql);
            
            // Extract customer information
            $customerName = $data['contact']['name'] ?? '';
            $customerEmail = $data['contact']['email'] ?? '';
            $customerPhone = $data['contact']['phone'] ?? null;
            $skinConcerns = $data['contact']['skinConcerns'] ?? null;
            
            // JSON encode array fields
            $skinTypesJson = json_encode($data['skinType']);
            $keyActivesJson = json_encode($data['keyActives']);
            $extractsJson = json_encode($data['extracts']);
            $boostersJson = json_encode($data['boosters']);
            
            $stmt->bindParam(':customer_name', $customerName);
            $stmt->bindParam(':customer_email', $customerEmail);
            $stmt->bindParam(':customer_phone', $customerPhone);
            $stmt->bindParam(':skin_concerns', $skinConcerns);
            $stmt->bindParam(':skin_types', $skinTypesJson);
            $stmt->bindParam(':base_format', $data['baseFormat']);
            $stmt->bindParam(':key_actives', $keyActivesJson);
            $stmt->bindParam(':extracts', $extractsJson);
            $stmt->bindParam(':boosters', $boostersJson);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error saving formulation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get formulation by ID
     * @param int $id Formulation ID
     * @return array|false
     */
    public function getFormulationById($id) {
        $sql = "SELECT * FROM formulations WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $result = $stmt->fetch();
            if ($result) {
                // Decode JSON fields and restructure for compatibility
                $result['skin_types'] = json_decode($result['skin_types'], true);
                $result['key_actives'] = json_decode($result['key_actives'], true);
                $result['extracts'] = json_decode($result['extracts'], true);
                $result['boosters'] = json_decode($result['boosters'], true);
                
                // Create contact info array for compatibility
                $result['contact_info'] = [
                    'name' => $result['customer_name'],
                    'email' => $result['customer_email'],
                    'phone' => $result['customer_phone'],
                    'skinConcerns' => $result['skin_concerns']
                ];
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting formulation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all formulations with pagination
     * @param int $limit
     * @param int $offset
     * @return array|false
     */
    public function getAllFormulations($limit = 50, $offset = 0) {
        $sql = "SELECT id, customer_name, customer_email, skin_types, base_format, key_actives, created_at 
                FROM formulations 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll();
            
            // Decode JSON fields
            foreach ($results as &$result) {
                $result['skin_types'] = json_decode($result['skin_types'], true);
                $result['key_actives'] = json_decode($result['key_actives'], true);
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error getting formulations: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Search formulations by customer email
     * @param string $email
     * @return array|false
     */
    public function getFormulationsByCustomerEmail($email) {
        $sql = "SELECT * FROM formulations WHERE customer_email = :email ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $results = $stmt->fetchAll();
            
            // Decode JSON fields for each result
            foreach ($results as &$result) {
                $result['skin_types'] = json_decode($result['skin_types'], true);
                $result['key_actives'] = json_decode($result['key_actives'], true);
                $result['extracts'] = json_decode($result['extracts'], true);
                $result['boosters'] = json_decode($result['boosters'], true);
                
                // Create contact info array for compatibility
                $result['contact_info'] = [
                    'name' => $result['customer_name'],
                    'email' => $result['customer_email'],
                    'phone' => $result['customer_phone'],
                    'skinConcerns' => $result['skin_concerns']
                ];
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error searching formulations: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get formulation statistics
     * @return array
     */
    public function getFormulationStats() {
        $sql = "SELECT 
                    COUNT(*) as total_formulations,
                    COUNT(DISTINCT customer_email) as unique_customers,
                    base_format,
                    COUNT(*) as format_count
                FROM formulations 
                GROUP BY base_format";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting stats: " . $e->getMessage());
            return false;
        }
    }
}
?>