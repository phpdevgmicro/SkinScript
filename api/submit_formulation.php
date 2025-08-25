<?php
/**
 * Formulation Submission API Endpoint
 * Handles AJAX form submissions from frontend
 */

// Set content type and allow CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Disable error output to prevent header issues
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Include required files
require_once __DIR__ . '/controllers/FormulationController.php';

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        exit;
    }

    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Check if data was received
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }

    // Restructure data if it comes in nested format from frontend
    if (isset($data['formulation']) && is_array($data['formulation'])) {
        $restructuredData = $data['formulation'];
        $restructuredData['contact'] = $data['contact'] ?? [];
        $data = $restructuredData;
    }

    // Initialize controller
    $controller = new FormulationController();

    // Process the formulation
    $result = $controller->submitFormulation($data);

    // Set appropriate HTTP status code
    if ($result['success']) {
        http_response_code(201); // Created
    } else {
        http_response_code(400); // Bad Request
    }

    // Return result
    echo json_encode($result);

} catch (Exception $e) {
    // Log error
    error_log("API Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred'
    ]);
}
?>