<?php
/**
 * Database Setup API
 * Initializes the full database schema with tables and data
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $db = new Database();
    
    if ($db->isFallbackMode()) {
        throw new Exception('Database is in fallback mode. Cannot setup schema.');
    }
    
    // Setup full schema
    $success = $db->setupFullSchema();
    
    if ($success) {
        $pdo = $db->getConnection();
        
        // Get table counts for verification
        $tables = [];
        try {
            $tables['formulations'] = $pdo->query("SELECT COUNT(*) FROM skincare_formulations")->fetchColumn();
            $tables['ingredients'] = $pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();
            $tables['compatibility'] = $pdo->query("SELECT COUNT(*) FROM ingredient_compatibility")->fetchColumn();
            $tables['status'] = $pdo->query("SELECT COUNT(*) FROM formulation_status")->fetchColumn();
        } catch (PDOException $e) {
            // Tables might not exist yet, that's okay
            error_log("Error getting table counts: " . $e->getMessage());
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Database schema setup completed successfully',
            'tables' => $tables,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        throw new Exception('Failed to setup database schema');
    }
    
} catch (Exception $e) {
    error_log("Database setup error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>