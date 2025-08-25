<?php
/**
 * PHP-based MCP Server for Skincare Ingredient Database
 * Connects directly to MySQL database for real-time ingredient data
 */

require_once __DIR__ . '/../config/database.php';

class PHPMCPServer {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->initializeIngredientDatabase();
    }
    
    /**
     * Initialize ingredient database with comprehensive skincare data (PostgreSQL compatible)
     */
    private function initializeIngredientDatabase() {
        // Create ingredients table if not exists (PostgreSQL syntax)
        $sql = "CREATE TABLE IF NOT EXISTS skincare_ingredients (
            id SERIAL PRIMARY KEY,
            ingredient_name VARCHAR(100) NOT NULL UNIQUE,
            category VARCHAR(20) NOT NULL CHECK (category IN ('active', 'extract', 'booster', 'base')),
            safe_min_percentage DECIMAL(4,2) NOT NULL,
            safe_max_percentage DECIMAL(4,2) NOT NULL,
            recommended_percentage DECIMAL(4,2) NOT NULL,
            benefits JSONB NOT NULL,
            compatible_with JSONB NOT NULL,
            incompatible_with JSONB NOT NULL,
            skin_types JSONB NOT NULL,
            ph_min DECIMAL(3,1) DEFAULT NULL,
            ph_max DECIMAL(3,1) DEFAULT NULL,
            warnings JSONB DEFAULT NULL,
            regulatory_status VARCHAR(50) DEFAULT 'approved',
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        // Create indexes separately for PostgreSQL
        $indexSql1 = "CREATE INDEX IF NOT EXISTS idx_ingredient_name ON skincare_ingredients (ingredient_name)";
        $indexSql2 = "CREATE INDEX IF NOT EXISTS idx_category ON skincare_ingredients (category)";
        
        try {
            $this->conn->exec($sql);
            $this->conn->exec($indexSql1);
            $this->conn->exec($indexSql2);
            $this->populateIngredientData();
        } catch (PDOException $e) {
            error_log("Error creating ingredients table: " . $e->getMessage());
        }
    }
    
    /**
     * Populate database with current ingredient data
     */
    private function populateIngredientData() {
        $ingredients = [
            [
                'ingredient_name' => 'caffeine',
                'category' => 'active',
                'safe_min_percentage' => 0.5,
                'safe_max_percentage' => 3.0,
                'recommended_percentage' => 1.0,
                'benefits' => json_encode(['Reduces puffiness', 'Improves circulation', 'Antioxidant properties']),
                'compatible_with' => json_encode(['hyaluronic-acid', 'niacinamide', 'peptides']),
                'incompatible_with' => json_encode(['retinol', 'vitamin-c']),
                'skin_types' => json_encode(['all', 'tired', 'puffy']),
                'ph_min' => 5.0,
                'ph_max' => 7.0,
                'warnings' => json_encode(['May cause sensitivity in high concentrations'])
            ],
            [
                'ingredient_name' => 'niacinamide',
                'category' => 'active',
                'safe_min_percentage' => 2.0,
                'safe_max_percentage' => 10.0,
                'recommended_percentage' => 5.0,
                'benefits' => json_encode(['Reduces oil production', 'Minimizes pores', 'Anti-inflammatory']),
                'compatible_with' => json_encode(['hyaluronic-acid', 'peptides', 'ceramides', 'caffeine']),
                'incompatible_with' => json_encode(['retinol', 'vitamin-c']),
                'skin_types' => json_encode(['oily', 'acne-prone', 'sensitive']),
                'ph_min' => 5.0,
                'ph_max' => 7.0,
                'warnings' => json_encode(['May cause flushing at high concentrations'])
            ],
            [
                'ingredient_name' => 'vitamin-c',
                'category' => 'active',
                'safe_min_percentage' => 5.0,
                'safe_max_percentage' => 20.0,
                'recommended_percentage' => 10.0,
                'benefits' => json_encode(['Antioxidant protection', 'Brightens skin', 'Stimulates collagen']),
                'compatible_with' => json_encode(['hyaluronic-acid', 'peptides', 'alpha-arbutin']),
                'incompatible_with' => json_encode(['retinol', 'niacinamide', 'copper-peptides']),
                'skin_types' => json_encode(['dull', 'aging', 'hyperpigmented']),
                'ph_min' => 3.0,
                'ph_max' => 4.0,
                'warnings' => json_encode(['May oxidize easily', 'Can cause irritation', 'Use with sunscreen'])
            ],
            [
                'ingredient_name' => 'retinol',
                'category' => 'active',
                'safe_min_percentage' => 0.1,
                'safe_max_percentage' => 1.0,
                'recommended_percentage' => 0.3,
                'benefits' => json_encode(['Anti-aging', 'Improves skin texture', 'Increases cell turnover']),
                'compatible_with' => json_encode(['hyaluronic-acid', 'ceramides', 'squalane']),
                'incompatible_with' => json_encode(['vitamin-c', 'niacinamide', 'salicylic-acid', 'caffeine']),
                'skin_types' => json_encode(['aging', 'acne-prone']),
                'ph_min' => 5.5,
                'ph_max' => 6.0,
                'warnings' => json_encode(['Use only at night', 'May cause initial irritation', 'Increases photosensitivity'])
            ],
            [
                'ingredient_name' => 'hyaluronic-acid',
                'category' => 'active',
                'safe_min_percentage' => 0.1,
                'safe_max_percentage' => 2.0,
                'recommended_percentage' => 1.0,
                'benefits' => json_encode(['Intense hydration', 'Plumps skin', 'Reduces fine lines']),
                'compatible_with' => json_encode(['all-ingredients']),
                'incompatible_with' => json_encode([]),
                'skin_types' => json_encode(['all', 'dry', 'dehydrated']),
                'ph_min' => 4.0,
                'ph_max' => 8.0,
                'warnings' => json_encode(['Apply to damp skin for best results'])
            ],
            [
                'ingredient_name' => 'neem',
                'category' => 'extract',
                'safe_min_percentage' => 0.1,
                'safe_max_percentage' => 2.0,
                'recommended_percentage' => 0.5,
                'benefits' => json_encode(['Antibacterial', 'Anti-inflammatory', 'Purifying']),
                'compatible_with' => json_encode(['all-actives']),
                'incompatible_with' => json_encode([]),
                'skin_types' => json_encode(['acne-prone', 'oily', 'problematic']),
                'ph_min' => 5.0,
                'ph_max' => 7.0,
                'warnings' => json_encode(['Patch test recommended'])
            ],
            [
                'ingredient_name' => 'bilberry',
                'category' => 'extract',
                'safe_min_percentage' => 0.1,
                'safe_max_percentage' => 3.0,
                'recommended_percentage' => 1.0,
                'benefits' => json_encode(['Antioxidant', 'Brightening', 'Anti-aging']),
                'compatible_with' => json_encode(['vitamin-c', 'niacinamide', 'peptides']),
                'incompatible_with' => json_encode([]),
                'skin_types' => json_encode(['all', 'dull', 'aging']),
                'ph_min' => 4.0,
                'ph_max' => 7.0,
                'warnings' => json_encode([])
            ],
            [
                'ingredient_name' => 'glycerin',
                'category' => 'booster',
                'safe_min_percentage' => 1.0,
                'safe_max_percentage' => 10.0,
                'recommended_percentage' => 3.0,
                'benefits' => json_encode(['Humectant', 'Hydrating', 'Barrier repair']),
                'compatible_with' => json_encode(['all-ingredients']),
                'incompatible_with' => json_encode([]),
                'skin_types' => json_encode(['all', 'dry']),
                'ph_min' => 4.0,
                'ph_max' => 8.0,
                'warnings' => json_encode([])
            ],
            [
                'ingredient_name' => 'ceramides',
                'category' => 'booster',
                'safe_min_percentage' => 0.1,
                'safe_max_percentage' => 5.0,
                'recommended_percentage' => 1.0,
                'benefits' => json_encode(['Barrier repair', 'Moisture retention', 'Anti-aging']),
                'compatible_with' => json_encode(['retinol', 'hyaluronic-acid', 'peptides']),
                'incompatible_with' => json_encode([]),
                'skin_types' => json_encode(['dry', 'sensitive', 'aging']),
                'ph_min' => 5.0,
                'ph_max' => 7.5,
                'warnings' => json_encode([])
            ],
            [
                'ingredient_name' => 'copper-peptides',
                'category' => 'booster',
                'safe_min_percentage' => 0.01,
                'safe_max_percentage' => 1.0,
                'recommended_percentage' => 0.1,
                'benefits' => json_encode(['Stimulates collagen', 'Wound healing', 'Anti-aging']),
                'compatible_with' => json_encode(['hyaluronic-acid', 'ceramides']),
                'incompatible_with' => json_encode(['vitamin-c', 'retinol']),
                'skin_types' => json_encode(['aging', 'damaged']),
                'ph_min' => 5.0,
                'ph_max' => 7.0,
                'warnings' => json_encode(['May cause blue discoloration at high concentrations'])
            ]
        ];
        
        // Insert ingredients using INSERT IGNORE to avoid duplicates
        foreach ($ingredients as $ingredient) {
            $sql = "INSERT IGNORE INTO skincare_ingredients (
                ingredient_name, category, safe_min_percentage, safe_max_percentage, 
                recommended_percentage, benefits, compatible_with, incompatible_with, 
                skin_types, ph_min, ph_max, warnings
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    $ingredient['ingredient_name'],
                    $ingredient['category'],
                    $ingredient['safe_min_percentage'],
                    $ingredient['safe_max_percentage'],
                    $ingredient['recommended_percentage'],
                    $ingredient['benefits'],
                    $ingredient['compatible_with'],
                    $ingredient['incompatible_with'],
                    $ingredient['skin_types'],
                    $ingredient['ph_min'],
                    $ingredient['ph_max'],
                    $ingredient['warnings']
                ]);
            } catch (PDOException $e) {
                error_log("Error inserting ingredient {$ingredient['ingredient_name']}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Get real-time ingredient information from database
     * @param string $ingredientName
     * @return array|null
     */
    public function getIngredientInfo($ingredientName) {
        $sql = "SELECT * FROM skincare_ingredients WHERE ingredient_name = ? LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([strtolower($ingredientName)]);
            $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ingredient) {
                // Decode JSON fields
                $ingredient['benefits'] = json_decode($ingredient['benefits'], true);
                $ingredient['compatible_with'] = json_decode($ingredient['compatible_with'], true);
                $ingredient['incompatible_with'] = json_decode($ingredient['incompatible_with'], true);
                $ingredient['skin_types'] = json_decode($ingredient['skin_types'], true);
                $ingredient['warnings'] = json_decode($ingredient['warnings'], true);
                
                return [
                    'success' => true,
                    'ingredient' => $ingredient,
                    'dataSource' => 'Real-time MySQL Database',
                    'lastUpdated' => $ingredient['last_updated']
                ];
            }
            
            return [
                'success' => false,
                'message' => "Ingredient '{$ingredientName}' not found in database",
                'available_ingredients' => $this->getAvailableIngredients()
            ];
            
        } catch (PDOException $e) {
            error_log("Error getting ingredient info: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Database error occurred'
            ];
        }
    }
    
    /**
     * Check ingredient compatibility using real-time database data
     * @param array $ingredients
     * @return array
     */
    public function checkIngredientCompatibility($ingredients) {
        if (!is_array($ingredients)) {
            return [
                'success' => false,
                'error' => 'Ingredients must be provided as an array'
            ];
        }
        
        $conflicts = [];
        $synergies = [];
        $ingredientData = [];
        
        // Get all ingredient data from database
        foreach ($ingredients as $ingredient) {
            $info = $this->getIngredientInfo($ingredient);
            if ($info['success']) {
                $ingredientData[$ingredient] = $info['ingredient'];
            }
        }
        
        // Check pairwise compatibility
        for ($i = 0; $i < count($ingredients); $i++) {
            for ($j = $i + 1; $j < count($ingredients); $j++) {
                $ing1Name = $ingredients[$i];
                $ing2Name = $ingredients[$j];
                
                if (isset($ingredientData[$ing1Name]) && isset($ingredientData[$ing2Name])) {
                    $ing1 = $ingredientData[$ing1Name];
                    $ing2 = $ingredientData[$ing2Name];
                    
                    // Check for incompatibilities
                    if (in_array(strtolower($ing2Name), $ing1['incompatible_with']) || 
                        in_array(strtolower($ing1Name), $ing2['incompatible_with'])) {
                        $conflicts[] = [
                            'ingredients' => [$ing1Name, $ing2Name],
                            'issue' => 'Chemical incompatibility detected - may reduce efficacy or cause irritation',
                            'severity' => 'high',
                            'source' => 'Real-time database analysis'
                        ];
                    }
                    
                    // Check for synergies
                    if (in_array(strtolower($ing2Name), $ing1['compatible_with']) || 
                        in_array(strtolower($ing1Name), $ing2['compatible_with']) ||
                        in_array('all-ingredients', $ing1['compatible_with']) ||
                        in_array('all-ingredients', $ing2['compatible_with'])) {
                        $synergies[] = [
                            'ingredients' => [$ing1Name, $ing2Name],
                            'benefit' => 'Synergistic combination - enhanced efficacy expected',
                            'source' => 'Real-time database analysis'
                        ];
                    }
                }
            }
        }
        
        return [
            'success' => true,
            'compatibilityAnalysis' => [
                'conflicts' => $conflicts,
                'synergies' => $synergies,
                'overallRating' => count($conflicts) === 0 ? 'excellent' : (count($conflicts) <= 1 ? 'good' : 'needs-review'),
                'recommendations' => count($conflicts) > 0 ? 
                    'Consider reformulating to avoid ingredient conflicts' : 
                    'All ingredients are compatible based on current database',
                'analysisDate' => date('Y-m-d H:i:s'),
                'dataSource' => 'Real-time MySQL Database'
            ]
        ];
    }
    
    /**
     * Calculate safe percentages based on database data and formulation parameters
     * @param array $ingredients
     * @param string $format
     * @param array $skinTypes
     * @return array
     */
    public function calculateSafePercentages($ingredients, $format = 'mist', $skinTypes = []) {
        $percentages = [];
        $warnings = [];
        $totalActivePercentage = 0;
        
        foreach ($ingredients as $ingredient) {
            $info = $this->getIngredientInfo($ingredient);
            
            if ($info['success']) {
                $ingredientData = $info['ingredient'];
                $safeRange = [
                    'min' => $ingredientData['safe_min_percentage'],
                    'max' => $ingredientData['safe_max_percentage'], 
                    'recommended' => $ingredientData['recommended_percentage']
                ];
                
                // Adjust for sensitive skin
                if (in_array('sensitive', $skinTypes)) {
                    $safeRange['recommended'] = min($safeRange['recommended'] * 0.7, $safeRange['max']);
                    $safeRange['max'] = min($safeRange['max'] * 0.8, $safeRange['max']);
                }
                
                // Adjust for format
                if ($format === 'mist' && $ingredientData['category'] === 'active') {
                    $safeRange['recommended'] = min($safeRange['recommended'] * 0.8, $safeRange['max']);
                }
                
                $percentages[$ingredient] = $safeRange;
                
                if ($ingredientData['category'] === 'active') {
                    $totalActivePercentage += $safeRange['recommended'];
                }
                
                // Add warnings
                if (!empty($ingredientData['warnings'])) {
                    $warnings[] = $ingredient . ': ' . implode(', ', $ingredientData['warnings']);
                }
            }
        }
        
        return [
            'success' => true,
            'safePercentages' => $percentages,
            'formulation' => [
                'format' => $format,
                'targetSkinTypes' => $skinTypes,
                'totalActivePercentage' => $totalActivePercentage,
                'phGuidance' => $this->getPhGuidance($ingredients),
                'stabilityNotes' => $this->getStabilityNotes($ingredients, $format)
            ],
            'warnings' => $warnings,
            'calculatedAt' => date('Y-m-d H:i:s'),
            'dataSource' => 'Real-time MySQL Database'
        ];
    }
    
    /**
     * Get pH guidance based on ingredients
     */
    private function getPhGuidance($ingredients) {
        $minPh = 7.0;
        $maxPh = 3.0;
        
        foreach ($ingredients as $ingredient) {
            $info = $this->getIngredientInfo($ingredient);
            if ($info['success'] && $info['ingredient']['ph_min'] && $info['ingredient']['ph_max']) {
                $minPh = min($minPh, $info['ingredient']['ph_min']);
                $maxPh = max($maxPh, $info['ingredient']['ph_max']);
            }
        }
        
        return [
            'min' => $minPh,
            'max' => $maxPh,
            'optimal' => ($minPh + $maxPh) / 2
        ];
    }
    
    /**
     * Get stability notes
     */
    private function getStabilityNotes($ingredients, $format) {
        $notes = [];
        
        if (in_array('vitamin-c', $ingredients)) {
            $notes[] = "Vitamin C requires opaque packaging and refrigeration for stability";
        }
        
        if (in_array('retinol', $ingredients)) {
            $notes[] = "Retinol degrades in light and air - use airless packaging";
        }
        
        if ($format === 'mist') {
            $notes[] = "Mist formulations require antimicrobial preservatives due to high water content";
        }
        
        return $notes;
    }
    
    /**
     * Get available ingredients list
     */
    private function getAvailableIngredients() {
        $sql = "SELECT ingredient_name FROM skincare_ingredients ORDER BY ingredient_name";
        
        try {
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error getting available ingredients: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get formulation recommendations based on skin type and database
     */
    public function getFormulationRecommendations($skinTypes, $format, $selectedIngredients = [], $concerns = '') {
        $recommendations = [
            'recommendedIngredients' => [],
            'avoidIngredients' => [],
            'skinTypeGuidance' => [],
            'enhancementSuggestions' => []
        ];
        
        // Get ingredients suitable for skin types (PostgreSQL JSONB syntax)
        foreach ($skinTypes as $skinType) {
            $placeholders = str_repeat('?,', count($selectedIngredients) - 1) . '?';
            $sql = "SELECT ingredient_name, benefits, recommended_percentage 
                    FROM skincare_ingredients 
                    WHERE skin_types ? ? 
                    AND ingredient_name NOT IN ({$placeholders})
                    ORDER BY recommended_percentage DESC LIMIT 5";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $params = array_merge([$skinType], $selectedIngredients);
                $stmt->execute($params);
                $suitableIngredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $recommendations['skinTypeGuidance'][$skinType] = $suitableIngredients;
            } catch (PDOException $e) {
                error_log("Error getting recommendations for skin type {$skinType}: " . $e->getMessage());
            }
        }
        
        // Add enhancement suggestions based on current selection
        if (in_array('caffeine', $selectedIngredients) && in_array('niacinamide', $selectedIngredients)) {
            $recommendations['enhancementSuggestions'][] = 
                "Excellent combination for oily, tired skin - consider adding hyaluronic acid for hydration balance";
        }
        
        return [
            'success' => true,
            'formulationGuidance' => $recommendations,
            'timestamp' => date('Y-m-d H:i:s'),
            'dataSource' => 'Real-time MySQL Database'
        ];
    }
}
?>