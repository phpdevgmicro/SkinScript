<?php
/**
 * API endpoint to get formulations for a specific customer
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../../api/models/FormulationModel.php';

try {
    if (!isset($_GET['email'])) {
        throw new Exception('Email parameter is required');
    }
    
    $email = trim($_GET['email']);
    if (empty($email)) {
        throw new Exception('Email cannot be empty');
    }
    
    $formulationModel = new FormulationModel();
    $formulations = $formulationModel->getFormulationsByCustomerEmail($email);
    
    if ($formulations === false) {
        throw new Exception('Failed to retrieve formulations');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'formulations' => $formulations,
        'count' => count($formulations)
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>