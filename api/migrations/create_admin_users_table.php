<?php
/**
 * Create Admin Users Table
 * Create the admin_users table for admin authentication
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Creating admin users table...\n\n";
    
    // Create the admin_users table
    $createTableSql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($createTableSql);
    echo "✅ Successfully created 'admin_users' table\n";
    
    // Create default admin user
    $defaultUsername = 'admin';
    $defaultPassword = 'skincraft2025';
    $defaultEmail = 'admin@skincraft.com';
    $defaultFullName = 'Admin User';
    
    // Check if admin user already exists
    $checkStmt = $conn->prepare("SELECT id FROM admin_users WHERE username = :username");
    $checkStmt->bindParam(':username', $defaultUsername);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() == 0) {
        // Create default admin user
        $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        $insertStmt = $conn->prepare("INSERT INTO admin_users (username, email, password_hash, full_name) VALUES (:username, :email, :password_hash, :full_name)");
        $insertStmt->bindParam(':username', $defaultUsername);
        $insertStmt->bindParam(':email', $defaultEmail);
        $insertStmt->bindParam(':password_hash', $passwordHash);
        $insertStmt->bindParam(':full_name', $defaultFullName);
        
        if ($insertStmt->execute()) {
            echo "✅ Created default admin user (username: admin, password: skincraft2025)\n";
        } else {
            echo "❌ Failed to create default admin user\n";
        }
    } else {
        echo "ℹ️ Admin user already exists\n";
    }
    
    // Create admin settings table for AI prompts
    $createSettingsTableSql = "CREATE TABLE IF NOT EXISTS admin_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL,
        description TEXT,
        updated_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL,
        INDEX idx_setting_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($createSettingsTableSql);
    echo "✅ Successfully created 'admin_settings' table\n";
    
    // Insert default AI prompt settings
    $defaultPrompts = [
        [
            'key' => 'ai_formulation_prompt',
            'value' => 'You are a professional cosmetic chemist. Based on the customer\'s skin type, concerns, and selected ingredients, create a detailed skincare formulation. Include exact percentages, INCI names, and brief explanations for each ingredient choice. Ensure the formulation is safe, effective, and stable.',
            'description' => 'AI prompt for generating formulation suggestions'
        ],
        [
            'key' => 'admin_notification_email',
            'value' => 'admin@skincraft.com',
            'description' => 'Email address to receive admin notifications'
        ]
    ];
    
    foreach ($defaultPrompts as $prompt) {
        $checkSettingStmt = $conn->prepare("SELECT id FROM admin_settings WHERE setting_key = :key");
        $checkSettingStmt->bindParam(':key', $prompt['key']);
        $checkSettingStmt->execute();
        
        if ($checkSettingStmt->rowCount() == 0) {
            $insertSettingStmt = $conn->prepare("INSERT INTO admin_settings (setting_key, setting_value, description) VALUES (:key, :value, :description)");
            $insertSettingStmt->bindParam(':key', $prompt['key']);
            $insertSettingStmt->bindParam(':value', $prompt['value']);
            $insertSettingStmt->bindParam(':description', $prompt['description']);
            $insertSettingStmt->execute();
            echo "✅ Created setting: " . $prompt['key'] . "\n";
        }
    }
    
    echo "\n🎉 Admin authentication system successfully set up!\n";
    echo "📊 You can now log in with:\n";
    echo "   - Username: admin\n";
    echo "   - Password: skincraft2025\n";
    echo "   - Change these credentials in the admin settings page\n";
    
} catch (Exception $e) {
    echo "❌ Error creating admin tables: " . $e->getMessage() . "\n";
    echo "Please check your database connection and try again.\n";
}
?>