<?php
/**
 * Generate AI Formulation for Admin
 * Allows admin to generate AI-powered formulation suggestions for customer requests
 */

// Start session and check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Set content type
header('Content-Type: application/json; charset=utf-8');

// Include required files
require_once '../../api/models/FormulationModel.php';
require_once '../../api/models/AdminUserModel.php';
require_once '../../api/services/OpenAIService.php';

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    $formulationId = $data['formulation_id'] ?? null;
    $customPrompt = $data['custom_prompt'] ?? null;

    if (!$formulationId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Formulation ID is required']);
        exit;
    }

    // Get formulation data
    $formulationModel = new FormulationModel();
    $formulation = $formulationModel->getFormulationById($formulationId);

    if (!$formulation) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Formulation not found']);
        exit;
    }

    // Get AI prompt from settings or use custom prompt
    $adminModel = new AdminUserModel();
    $aiPrompt = $customPrompt ?: $adminModel->getSetting('ai_formulation_prompt');

    if (!$aiPrompt) {
        $aiPrompt = 'You are a professional cosmetic chemist. Based on the customer\'s skin type, concerns, and selected ingredients, create a detailed skincare formulation. Include exact percentages, INCI names, and brief explanations for each ingredient choice. Ensure the formulation is safe, effective, and stable.';
    }

    // Prepare formulation data for AI
    $formulationData = [
        'skinType' => $formulation['skin_types'],
        'baseFormat' => $formulation['base_format'],
        'keyActives' => $formulation['key_actives'],
        'extracts' => $formulation['extracts'],
        'boosters' => $formulation['boosters'],
        'contact' => [
            'name' => $formulation['customer_name'],
            'email' => $formulation['customer_email'],
            'concerns' => $formulation['skin_concerns']
        ]
    ];

    // Generate AI formulation
    $openAIService = new OpenAIService();
    
    // Set custom prompt if provided
    if ($customPrompt) {
        $openAIService->setCustomPrompt($customPrompt);
    }
    
    $suggestions = $openAIService->generateFormulation($formulationData);

    if ($suggestions) {
        // Generate product description as well
        $suggestions['product_description'] = $openAIService->generateProductDescription($formulationData, $suggestions);
        
        // Update formulation with AI suggestions (optional - store for reference)
        $updateSql = "UPDATE formulations SET ai_suggestions = :suggestions, ai_generated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $database = new Database();
        $conn = $database->getConnection();
        $stmt = $conn->prepare($updateSql);
        $stmt->bindParam(':suggestions', json_encode($suggestions));
        $stmt->bindParam(':id', $formulationId);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'AI formulation generated successfully',
            'suggestions' => $suggestions,
            'formulation_id' => $formulationId
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to generate AI formulation'
        ]);
    }

} catch (Exception $e) {
    error_log("AI Formulation Generation Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred: ' . $e->getMessage()
    ]);
}
?>