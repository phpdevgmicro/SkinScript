<?php
/**
 * Database Connection Test
 * Quick test to verify MySQL connection is working
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/FormulationModel.php';

header('Content-Type: text/plain');

try {
    echo "Testing database connection...\n\n";
    
    // Test basic connection
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✓ Database connection successful\n";
        
        // Test model initialization
        $model = new FormulationModel();
        $tableCreated = $model->createTableIfNotExists();
        
        if ($tableCreated) {
            echo "✓ Formulations table ready\n";
            
            // Test a simple query
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM formulations");
            $stmt->execute();
            $result = $stmt->fetch();
            
            echo "✓ Database query successful\n";
            echo "✓ Current formulations count: " . $result['count'] . "\n";
            
            echo "\n🎉 Backend setup complete and ready!\n";
            
        } else {
            echo "❌ Failed to create/verify table\n";
        }
        
    } else {
        echo "❌ Database connection failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>