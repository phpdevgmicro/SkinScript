<?php
/**
 * PDF Preview API Endpoint
 * Generates a preview of the formulation PDF without saving or submitting
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
require_once __DIR__ . '/services/PDFService.php';
require_once __DIR__ . '/services/OpenAIService.php';

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
        $formulation = $data['formulation'];
        $formulation['contact'] = $data['contact'] ?? [];
    } else {
        $formulation = $data;
    }

    // Validate required fields
    if (empty($formulation['skinType']) || empty($formulation['keyActives'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: skin type and key actives'
        ]);
        exit;
    }

    // Generate AI suggestions for the preview
    $openAIService = new OpenAIService();
    $aiSuggestions = $openAIService->generateFormulation($formulation);

    // Generate PDF preview
    $pdfService = new PDFService();
    $pdfResult = $pdfService->generateFormulationPDF($formulation, $aiSuggestions);

    // Prepare response
    $response = [
        'success' => true,
        'message' => 'PDF preview generated successfully',
        'formulation_data' => $formulation,
        'ai_suggestions' => $aiSuggestions,
        'pdf_preview' => [
            'available' => $pdfResult['success'],
            'content_base64' => $pdfResult['content'] ?? null,
            'html_fallback' => !$pdfResult['success'] ? $pdfResult['content'] : null,
            'filename' => $pdfResult['filename'] ?? 'preview.pdf',
            'type' => $pdfResult['type'] ?? 'application/pdf'
        ],
        'preview_id' => 'preview_' . time()
    ];

    // Add ingredient descriptions
    $response['ingredient_descriptions'] = [
        'actives' => getActiveIngredientDescriptions($formulation['keyActives']),
        'extracts' => getBotanicalExtractDescriptions($formulation['extracts'] ?? []),
        'hydrators' => getHydratorDescriptions($formulation['boosters'] ?? [])
    ];

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    // Log error
    error_log("PDF Preview Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate PDF preview'
    ]);
}

/**
 * Get descriptions for active ingredients
 */
function getActiveIngredientDescriptions($actives) {
    $descriptions = [
        'caffeine' => 'Energizing compound that boosts circulation and reduces puffiness',
        'retinol' => 'Vitamin A derivative that promotes cell turnover and reduces signs of aging',
        'niacinamide' => 'Form of Vitamin B3 that minimizes pores and evens skin tone',
        'vitamin-c' => 'Powerful antioxidant that brightens skin and protects from environmental damage',
        'hyaluronic-acid' => 'Humectant that holds up to 1000x its weight in water for deep hydration',
        'salicylic-acid' => 'Beta hydroxy acid that gently exfoliates and unclogs pores'
    ];
    
    $result = [];
    foreach ($actives as $active) {
        $result[$active] = $descriptions[$active] ?? 'Professional-grade active ingredient for targeted skin benefits';
    }
    return $result;
}

/**
 * Get descriptions for botanical extracts
 */
function getBotanicalExtractDescriptions($extracts) {
    $descriptions = [
        'neem' => 'Natural antibacterial and purifying extract from the neem tree',
        'green-tea' => 'Rich in antioxidants, provides soothing and anti-inflammatory benefits',
        'chamomile' => 'Gentle, calming extract that reduces irritation and redness',
        'avena-sativa' => 'Oat extract that soothes sensitive skin and provides protective barrier',
        'beta-vulgaris' => 'Beetroot extract rich in vitamins and minerals for skin vitality',
        'bilberry' => 'Antioxidant-rich berry extract that brightens and protects skin'
    ];
    
    $result = [];
    foreach ($extracts as $extract) {
        $result[$extract] = $descriptions[$extract] ?? 'Natural botanical extract for enhanced skin benefits';
    }
    return $result;
}

/**
 * Get descriptions for hydrators
 */
function getHydratorDescriptions($hydrators) {
    $descriptions = [
        'glycerin' => 'Humectant that draws moisture from the air to keep skin hydrated',
        'sodium-pca' => 'Natural moisturizing factor that maintains skin hydration balance',
        'ceramides' => 'Lipid molecules that repair and strengthen the skin barrier',
        'squalane' => 'Lightweight, non-comedogenic oil that provides lasting moisture',
        'copper-peptides' => 'Bioactive peptides that support collagen production and skin healing'
    ];
    
    $result = [];
    foreach ($hydrators as $hydrator) {
        $result[$hydrator] = $descriptions[$hydrator] ?? 'Advanced hydrating ingredient for optimal skin moisture';
    }
    return $result;
}
?>