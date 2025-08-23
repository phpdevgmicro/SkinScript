<?php
/**
 * Formulation Submission API
 * Handles secure form submission to PostgreSQL database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

require_once '../config/database.php';

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    $required_fields = ['formulation', 'contact'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $formulation = $data['formulation'];
    $contact = $data['contact'];
    
    // Validate contact information
    if (empty($contact['fullName']) || empty($contact['email'])) {
        throw new Exception('Full name and email are required');
    }
    
    // Validate email format
    if (!filter_var($contact['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Validate formulation data
    if (empty($formulation['skinType']) || empty($formulation['keyActives'])) {
        throw new Exception('Skin type and key actives are required');
    }
    
    // Connect to database
    $db = new Database();
    
    if ($db->isFallbackMode()) {
        // Fallback mode - log to file
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'contact' => $contact,
            'formulation' => $formulation,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'screen_resolution' => $data['screenResolution'] ?? '',
            'form_version' => $data['formVersion'] ?? '1.0'
        ];
        
        $logFile = '../logs/formulations.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
        
        // Generate a temporary ID
        $formulation_id = 'TEMP_' . time() . '_' . substr(md5($contact['email']), 0, 8);
        
        error_log("Formulation logged locally (fallback mode) - ID: $formulation_id, Email: " . $contact['email']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Formulation submitted successfully (stored locally)',
            'id' => $formulation_id,
            'timestamp' => date('Y-m-d H:i:s'),
            'fallback_mode' => true
        ]);
        
    } else {
        // Normal database mode
        $pdo = $db->getConnection();
        
        // Prepare SQL statement
        $sql = "INSERT INTO skincare_formulations 
                (full_name, email, skin_concerns, skin_type, base_format, key_actives, extracts, boosters, user_agent, screen_resolution, form_version)
                VALUES (:full_name, :email, :skin_concerns, :skin_type, :base_format, :key_actives, :extracts, :boosters, :user_agent, :screen_resolution, :form_version)";
        
        $stmt = $pdo->prepare($sql);
        
        // Convert arrays to PostgreSQL array format
        $skin_type_array = '{' . implode(',', array_map('trim', $formulation['skinType'])) . '}';
        $key_actives_array = '{' . implode(',', array_map('trim', $formulation['keyActives'])) . '}';
        $extracts_array = !empty($formulation['extracts']) ? '{' . implode(',', array_map('trim', $formulation['extracts'])) . '}' : '{}';
        $boosters_array = !empty($formulation['boosters']) ? '{' . implode(',', array_map('trim', $formulation['boosters'])) . '}' : '{}';
        
        // Execute statement
        $result = $stmt->execute([
            ':full_name' => trim($contact['fullName']),
            ':email' => trim($contact['email']),
            ':skin_concerns' => trim($contact['skinConcerns'] ?? ''),
            ':skin_type' => $skin_type_array,
            ':base_format' => trim($formulation['baseFormat']),
            ':key_actives' => $key_actives_array,
            ':extracts' => $extracts_array,
            ':boosters' => $boosters_array,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ':screen_resolution' => $data['screenResolution'] ?? '',
            ':form_version' => $data['formVersion'] ?? '1.0'
        ]);
        
        if ($result) {
            $formulation_id = $pdo->lastInsertId();
            
            // Log success
            error_log("Formulation submitted successfully - ID: $formulation_id, Email: " . $contact['email']);
            
            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Formulation submitted successfully',
                'id' => $formulation_id,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            throw new Exception('Failed to save formulation');
        }
    }
    
} catch (Exception $e) {
    error_log("Formulation submission error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>