<?php
/**
 * Database Connection Test
 * Simple endpoint to test Supabase PostgreSQL connectivity
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $db = new Database();
    
    if ($db->isFallbackMode()) {
        echo json_encode([
            'success' => false,
            'message' => 'Running in fallback mode - external database unavailable',
            'fallback_mode' => true,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else if ($db->testConnection()) {
        echo json_encode([
            'success' => true,
            'message' => 'Database connection successful',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        throw new Exception('Database connection test failed');
    }
    
} catch (Exception $e) {
    error_log("Database connection test error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>