<?php
/**
 * Admin User Model
 * Handles admin user database operations
 */

require_once __DIR__ . '/../config/database.php';

class AdminUserModel {
    private $database;
    private $conn;

    public function __construct() {
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
    }

    /**
     * Authenticate admin user
     * @param string $username
     * @param string $password
     * @return array|false
     */
    public function authenticateUser($username, $password) {
        try {
            $sql = "SELECT id, username, email, password_hash, full_name, is_active FROM admin_users WHERE username = :username AND is_active = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Update last login
                $updateSql = "UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
                $updateStmt = $this->conn->prepare($updateSql);
                $updateStmt->bindParam(':id', $user['id']);
                $updateStmt->execute();
                
                return $user;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get admin user by ID
     * @param int $id
     * @return array|false
     */
    public function getUserById($id) {
        try {
            $sql = "SELECT id, username, email, full_name, is_active, last_login, created_at FROM admin_users WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update admin user password
     * @param int $id
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($id, $newPassword) {
        try {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE admin_users SET password_hash = :password_hash WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Update password error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get admin setting
     * @param string $key
     * @return string|false
     */
    public function getSetting($key) {
        try {
            $sql = "SELECT setting_value FROM admin_settings WHERE setting_key = :key";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':key', $key);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return $result ? $result['setting_value'] : false;
        } catch (Exception $e) {
            error_log("Get setting error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update admin setting
     * @param string $key
     * @param string $value
     * @param int $updatedBy
     * @return bool
     */
    public function updateSetting($key, $value, $updatedBy = null) {
        try {
            $sql = "UPDATE admin_settings SET setting_value = :value, updated_by = :updated_by WHERE setting_key = :key";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':updated_by', $updatedBy);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Update setting error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all admin settings
     * @return array|false
     */
    public function getAllSettings() {
        try {
            $sql = "SELECT setting_key, setting_value, description, updated_at FROM admin_settings ORDER BY setting_key";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get all settings error: " . $e->getMessage());
            return false;
        }
    }
}
?>