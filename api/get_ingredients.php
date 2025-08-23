<?php
/**
 * Ingredients API
 * Returns ingredient data and compatibility information
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $db = new Database();
    
    if ($db->isFallbackMode()) {
        // Return static data if database is unavailable
        $staticData = [
            'ingredients' => [
                'actives' => [
                    ['name' => 'caffeine', 'benefits' => ['anti-puffiness', 'energizing']],
                    ['name' => 'l-carnitine', 'benefits' => ['energizing', 'metabolism-boosting']],
                    ['name' => 'retinol', 'benefits' => ['anti-aging', 'cell-renewal']],
                    ['name' => 'niacinamide', 'benefits' => ['pore-minimizing', 'oil-control']],
                    ['name' => 'vitamin-c', 'benefits' => ['brightening', 'antioxidant']],
                    ['name' => 'hyaluronic-acid', 'benefits' => ['hydrating', 'plumping']]
                ],
                'extracts' => [
                    ['name' => 'beta-vulgaris', 'benefits' => ['antioxidant', 'energizing']],
                    ['name' => 'avena-sativa', 'benefits' => ['soothing', 'moisturizing']],
                    ['name' => 'neem', 'benefits' => ['antibacterial', 'purifying']],
                    ['name' => 'bilberry', 'benefits' => ['antioxidant', 'brightening']],
                    ['name' => 'green-tea', 'benefits' => ['antioxidant', 'soothing']],
                    ['name' => 'chamomile', 'benefits' => ['soothing', 'calming']]
                ],
                'boosters' => [
                    ['name' => 'glycerin', 'benefits' => ['hydrating', 'smoothing']],
                    ['name' => 'sodium-pca', 'benefits' => ['hydrating', 'moisture-balance']],
                    ['name' => 'copper-peptides', 'benefits' => ['anti-aging', 'healing']],
                    ['name' => 'ceramides', 'benefits' => ['barrier-repair', 'protective']],
                    ['name' => 'squalane', 'benefits' => ['moisturizing', 'non-comedogenic']]
                ]
            ],
            'compatibility' => [
                ['ingredient_1' => 'retinol', 'ingredient_2' => 'vitamin-c', 'status' => 'incompatible'],
                ['ingredient_1' => 'retinol', 'ingredient_2' => 'niacinamide', 'status' => 'caution'],
                ['ingredient_1' => 'vitamin-c', 'ingredient_2' => 'niacinamide', 'status' => 'caution']
            ],
            'fallback_mode' => true
        ];
        
        echo json_encode($staticData);
        return;
    }
    
    $pdo = $db->getConnection();
    
    // Get ingredients by category
    $ingredients = [];
    $categories = ['active', 'extract', 'booster'];
    
    foreach ($categories as $category) {
        $stmt = $pdo->prepare("SELECT name, description, benefits, skin_types, concentration_range FROM ingredients WHERE category = ? AND is_active = true ORDER BY name");
        $stmt->execute([$category]);
        $ingredients[$category . 's'] = $stmt->fetchAll();
    }
    
    // Get compatibility rules
    $stmt = $pdo->prepare("SELECT ingredient_1, ingredient_2, compatibility_status, notes FROM ingredient_compatibility");
    $stmt->execute();
    $compatibility = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'ingredients' => $ingredients,
        'compatibility' => $compatibility,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log("Ingredients API error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>