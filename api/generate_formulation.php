<?php
/**
 * Formulation Generation API
 * Creates personalized formulations based on user selections
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

require_once '../engine/FormulationEngine.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    if (empty($data['skinType']) || empty($data['baseFormat']) || empty($data['keyActives'])) {
        throw new Exception('Missing required formulation data');
    }
    
    // Create formulation engine
    $engine = new FormulationEngine();
    
    // Generate formulation
    $formulation = $engine->generateFormulation(
        $data['skinType'],
        $data['baseFormat'],
        $data['keyActives'],
        $data['extracts'] ?? [],
        $data['boosters'] ?? []
    );
    
    // Add metadata
    $formulation['generated_at'] = date('Y-m-d H:i:s');
    $formulation['formulation_id'] = 'FORM_' . time() . '_' . substr(md5(json_encode($data)), 0, 8);
    
    echo json_encode([
        'success' => true,
        'formulation' => $formulation
    ]);
    
} catch (Exception $e) {
    error_log("Formulation generation error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>