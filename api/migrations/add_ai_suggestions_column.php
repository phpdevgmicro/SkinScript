<?php
/**
 * Add AI Suggestions Column to Formulations Table
 * Add ai_suggestions and ai_generated_at columns for storing AI formulation suggestions
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Adding AI suggestions columns to formulations table...\n\n";
    
    // Check if columns already exist
    $checkSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = 'formulations' 
                 AND COLUMN_NAME IN ('ai_suggestions', 'ai_generated_at')";
    
    $stmt = $conn->prepare($checkSql);
    $stmt->execute();
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('ai_suggestions', $existingColumns)) {
        $addAISuggestionsSql = "ALTER TABLE formulations ADD COLUMN ai_suggestions LONGTEXT NULL";
        $conn->exec($addAISuggestionsSql);
        echo "✅ Added 'ai_suggestions' column\n";
    } else {
        echo "ℹ️ 'ai_suggestions' column already exists\n";
    }
    
    if (!in_array('ai_generated_at', $existingColumns)) {
        $addAIGeneratedAtSql = "ALTER TABLE formulations ADD COLUMN ai_generated_at TIMESTAMP NULL";
        $conn->exec($addAIGeneratedAtSql);
        echo "✅ Added 'ai_generated_at' column\n";
    } else {
        echo "ℹ️ 'ai_generated_at' column already exists\n";
    }
    
    echo "\n🎉 AI suggestions columns successfully added!\n";
    echo "📊 Formulations table can now store:\n";
    echo "   - AI generated formulation suggestions (JSON format)\n";
    echo "   - Timestamp when AI suggestions were generated\n";
    
} catch (Exception $e) {
    echo "❌ Error adding AI suggestions columns: " . $e->getMessage() . "\n";
    echo "Please check your database connection and try again.\n";
}
?>