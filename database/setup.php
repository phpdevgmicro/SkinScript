<?php
/**
 * Database Setup Script
 * Creates tables and populates initial data for the skincare formulation app
 */

require_once '../config/database.php';

try {
    echo "Setting up database schema...\n";
    
    $db = new Database();
    
    if ($db->isFallbackMode()) {
        echo "Warning: Database is in fallback mode. Cannot create schema.\n";
        echo "Please check your database connection settings.\n";
        exit(1);
    }
    
    $pdo = $db->getConnection();
    
    // Read and execute schema file
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    $schema = file_get_contents($schemaFile);
    
    // Execute schema (split by semicolon for multiple statements)
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "✗ Error executing statement: " . $e->getMessage() . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
    
    echo "\nDatabase schema setup completed successfully!\n";
    
    // Verify tables were created
    $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nCreated tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // Get counts
    echo "\nData summary:\n";
    try {
        $formulations = $pdo->query("SELECT COUNT(*) FROM skincare_formulations")->fetchColumn();
        $ingredients = $pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();
        $compatibility = $pdo->query("SELECT COUNT(*) FROM ingredient_compatibility")->fetchColumn();
        
        echo "- Formulations: $formulations\n";
        echo "- Ingredients: $ingredients\n";
        echo "- Compatibility rules: $compatibility\n";
    } catch (PDOException $e) {
        echo "- Could not retrieve counts: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>