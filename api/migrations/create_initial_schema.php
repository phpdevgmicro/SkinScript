<?php
/**
 * Create Initial Database Schema
 * Create the formulations table for skincare formulation app
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Creating initial database schema...\n\n";
    
    // Create the formulations table
    $createTableSql = "CREATE TABLE IF NOT EXISTS formulations (
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
    
    $conn->exec($createTableSql);
    echo "✅ Successfully created 'formulations' table\n";
    
    // Verify table structure
    $describeTable = "DESCRIBE formulations";
    $stmt = $conn->prepare($describeTable);
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "\n📋 Table structure:\n";
    echo "+-----------------+------------------+------+-----+---------+----------------+\n";
    echo "| Field           | Type             | Null | Key | Default | Extra          |\n";
    echo "+-----------------+------------------+------+-----+---------+----------------+\n";
    
    foreach ($columns as $column) {
        printf("| %-15s | %-16s | %-4s | %-3s | %-7s | %-14s |\n",
            $column['Field'],
            $column['Type'],
            $column['Null'],
            $column['Key'],
            $column['Default'] ?? 'NULL',
            $column['Extra']
        );
    }
    echo "+-----------------+------------------+------+-----+---------+----------------+\n";
    
    echo "\n🎉 Database schema successfully created and ready for use!\n";
    echo "📊 The table can store:\n";
    echo "   - Customer information (name, email, phone)\n";
    echo "   - Skin concerns and types (JSON format)\n";
    echo "   - Formulation details (base format, actives, extracts, boosters)\n";
    echo "   - Timestamps for creation and updates\n";
    echo "   - Proper indexing for fast queries\n";
    
} catch (Exception $e) {
    echo "❌ Error creating schema: " . $e->getMessage() . "\n";
    echo "Please check your database connection and try again.\n";
}
?>