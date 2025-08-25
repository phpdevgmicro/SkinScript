<?php
/**
 * Database Schema Migration
 * Migrate from JSON contact_info to separate customer columns
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Starting database schema migration...\n\n";
    
    // Check if old table exists and has data
    $checkOldTable = "SHOW COLUMNS FROM formulations LIKE 'contact_info'";
    $stmt = $conn->prepare($checkOldTable);
    $stmt->execute();
    $hasOldSchema = $stmt->rowCount() > 0;
    
    if ($hasOldSchema) {
        echo "Found old schema with contact_info column.\n";
        
        // Get existing data
        $getData = "SELECT * FROM formulations";
        $stmt = $conn->prepare($getData);
        $stmt->execute();
        $existingData = $stmt->fetchAll();
        
        echo "Found " . count($existingData) . " existing records.\n";
        
        // Create backup table
        $backupSql = "CREATE TABLE formulations_backup AS SELECT * FROM formulations";
        $conn->exec($backupSql);
        echo "Created backup table: formulations_backup\n";
        
        // Drop old table
        $conn->exec("DROP TABLE formulations");
        echo "Dropped old table.\n";
        
        // Create new table with proper schema
        $newTableSql = "CREATE TABLE formulations (
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
        
        $conn->exec($newTableSql);
        echo "Created new table with proper schema.\n";
        
        // Migrate existing data
        if (!empty($existingData)) {
            $insertSql = "INSERT INTO formulations (
                customer_name, customer_email, customer_phone, skin_concerns,
                skin_types, base_format, key_actives, extracts, boosters, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insertStmt = $conn->prepare($insertSql);
            
            foreach ($existingData as $row) {
                $contactInfo = json_decode($row['contact_info'], true);
                
                $customerName = $contactInfo['name'] ?? $contactInfo['fullName'] ?? 'Unknown';
                $customerEmail = $contactInfo['email'] ?? 'unknown@email.com';
                $customerPhone = $contactInfo['phone'] ?? null;
                $skinConcerns = $contactInfo['skinConcerns'] ?? $contactInfo['concerns'] ?? null;
                
                $insertStmt->execute([
                    $customerName,
                    $customerEmail,
                    $customerPhone,
                    $skinConcerns,
                    $row['skin_types'],
                    $row['base_format'],
                    $row['key_actives'],
                    $row['extracts'],
                    $row['boosters'],
                    $row['created_at']
                ]);
            }
            
            echo "Migrated " . count($existingData) . " records to new schema.\n";
        }
        
        echo "\n✅ Migration completed successfully!\n";
        echo "📝 Backup stored in formulations_backup table\n";
        echo "🗑️  You can drop the backup table once you've verified the migration\n";
        
    } else {
        echo "No migration needed - schema is already up to date.\n";
        
        // Just ensure the table exists with correct schema
        $newTableSql = "CREATE TABLE IF NOT EXISTS formulations (
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
        
        $conn->exec($newTableSql);
        echo "Ensured table exists with correct schema.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Please check the error and try again.\n";
}
?>