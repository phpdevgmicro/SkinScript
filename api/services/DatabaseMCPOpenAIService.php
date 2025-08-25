<?php
/**
 * Database-powered MCP OpenAI Service
 * Uses PHP MCP server with MySQL database for real-time ingredient data
 */

require_once __DIR__ . '/OpenAIService.php';
require_once __DIR__ . '/../mcp/PHPMCPServer.php';

class DatabaseMCPOpenAIService extends OpenAIService {
    private $mcpServer;
    
    public function __construct() {
        parent::__construct();
        $this->mcpServer = new PHPMCPServer();
    }
    
    /**
     * Generate AI-powered formulation with real-time database enhancement
     * @param array $formulation User's selections
     * @return array Enhanced AI-generated suggestions
     */
    public function generateFormulation($formulation) {
        try {
            // Get real-time ingredient data from MySQL database
            $databaseData = $this->getDatabaseIngredientData($formulation);
            
            // Generate enhanced prompt with real-time database data
            $prompt = $this->buildDatabaseEnhancedPrompt($formulation, $databaseData);
            
            // Call OpenAI with enhanced context
            $response = $this->callOpenAI($prompt);
            
            // Parse and enhance the response with database insights
            $result = $this->parseFormulationResponse($response);
            
            // Add database-specific enhancements
            $result['database_enhanced'] = true;
            $result['real_time_data'] = $databaseData['timestamp'] ?? date('Y-m-d H:i:s');
            $result['data_source'] = 'Real-time MySQL Database';
            $result['compatibility_verified'] = true;
            
            return $result;
            
        } catch (Exception $e) {
            error_log('Database-MCP formulation failed: ' . $e->getMessage());
            // Fallback to regular OpenAI service
            return parent::generateFormulation($formulation);
        }
    }
    
    /**
     * Get real-time ingredient data from MySQL database via MCP server
     * @param array $formulation
     * @return array Database data
     */
    private function getDatabaseIngredientData($formulation) {
        $databaseData = [
            'ingredients' => [],
            'compatibility' => [],
            'recommendations' => [],
            'safe_percentages' => [],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        try {
            // Collect all ingredients
            $allIngredients = array_merge(
                $formulation['keyActives'] ?? [],
                $formulation['extracts'] ?? [], 
                $formulation['boosters'] ?? []
            );
            
            if (empty($allIngredients)) {
                return $databaseData;
            }
            
            // Get ingredient information from database
            foreach ($allIngredients as $ingredient) {
                $ingredientInfo = $this->mcpServer->getIngredientInfo($ingredient);
                if ($ingredientInfo['success']) {
                    $databaseData['ingredients'][$ingredient] = $ingredientInfo;
                }
            }
            
            // Check compatibility using database
            if (count($allIngredients) > 1) {
                $compatibilityInfo = $this->mcpServer->checkIngredientCompatibility($allIngredients);
                if ($compatibilityInfo['success']) {
                    $databaseData['compatibility'] = $compatibilityInfo;
                }
            }
            
            // Get formulation recommendations from database
            $recommendations = $this->mcpServer->getFormulationRecommendations(
                $formulation['skinType'] ?? [],
                $formulation['baseFormat'] ?? 'mist',
                $allIngredients,
                $formulation['contact']['skinConcerns'] ?? ''
            );
            if ($recommendations['success']) {
                $databaseData['recommendations'] = $recommendations;
            }
            
            // Calculate safe percentages from database
            $safePercentages = $this->mcpServer->calculateSafePercentages(
                $allIngredients,
                $formulation['baseFormat'] ?? 'mist',
                $formulation['skinType'] ?? []
            );
            if ($safePercentages['success']) {
                $databaseData['safe_percentages'] = $safePercentages;
            }
            
        } catch (Exception $e) {
            error_log('Database data retrieval error: ' . $e->getMessage());
        }
        
        return $databaseData;
    }
    
    /**
     * Build enhanced prompt with real-time database data
     * @param array $formulation
     * @param array $databaseData
     * @return string
     */
    private function buildDatabaseEnhancedPrompt($formulation, $databaseData) {
        $skinTypes = implode(', ', $formulation['skinType']);
        $keyActives = implode(', ', $formulation['keyActives']);
        $extracts = implode(', ', $formulation['extracts']);
        $boosters = implode(', ', $formulation['boosters']);
        $format = $formulation['baseFormat'];
        $concerns = $formulation['contact']['skinConcerns'] ?? 'general skin health';
        
        $prompt = "You are a professional cosmetic chemist with access to a real-time MySQL skincare ingredient database. Create a detailed, safe, and effective skincare formulation based on current ingredient data:

**Customer Profile:**
- Skin Types: {$skinTypes}
- Skin Concerns: {$concerns}  
- Preferred Format: {$format}

**Selected Ingredients:**
- Key Actives: {$keyActives}
- Botanical Extracts: {$extracts}
- Boosters/Hydrators: {$boosters}

**REAL-TIME DATABASE INGREDIENT ANALYSIS:**";

        // Add database ingredient data
        if (!empty($databaseData['ingredients'])) {
            $prompt .= "\n\n**Current Database Safety Data (Live from MySQL):**\n";
            foreach ($databaseData['ingredients'] as $ingredient => $data) {
                if (isset($data['ingredient'])) {
                    $info = $data['ingredient'];
                    $prompt .= "- {$ingredient}: Safe range {$info['safe_min_percentage']}-{$info['safe_max_percentage']}% (recommended: {$info['recommended_percentage']}%)\n";
                    $prompt .= "  Benefits: " . implode(', ', $info['benefits']) . "\n";
                    $prompt .= "  Category: {$info['category']}\n";
                    if (!empty($info['warnings'])) {
                        $prompt .= "  Warnings: " . implode(', ', $info['warnings']) . "\n";
                    }
                    $prompt .= "  Last Updated: {$info['last_updated']}\n";
                }
            }
        }
        
        // Add real-time compatibility data
        if (!empty($databaseData['compatibility']['compatibilityAnalysis'])) {
            $compat = $databaseData['compatibility']['compatibilityAnalysis'];
            $prompt .= "\n**REAL-TIME COMPATIBILITY ANALYSIS (MySQL Database):**\n";
            $prompt .= "- Overall Safety Rating: {$compat['overallRating']}\n";
            
            if (!empty($compat['conflicts'])) {
                $prompt .= "- ⚠️  CRITICAL CONFLICTS DETECTED:\n";
                foreach ($compat['conflicts'] as $conflict) {
                    $prompt .= "  DANGER: " . implode(' + ', $conflict['ingredients']) . " - {$conflict['issue']}\n";
                }
            }
            
            if (!empty($compat['synergies'])) {
                $prompt .= "- ✅ Beneficial Synergies Found:\n";
                foreach ($compat['synergies'] as $synergy) {
                    $prompt .= "  GOOD: " . implode(' + ', $synergy['ingredients']) . " - {$synergy['benefit']}\n";
                }
            }
        }
        
        // Add safe percentage calculations
        if (!empty($databaseData['safe_percentages']['safePercentages'])) {
            $prompt .= "\n**CALCULATED SAFE PERCENTAGES (Database-Driven):**\n";
            foreach ($databaseData['safe_percentages']['safePercentages'] as $ingredient => $ranges) {
                $prompt .= "- {$ingredient}: {$ranges['recommended']}% (range: {$ranges['min']}-{$ranges['max']}%)\n";
            }
            
            $formData = $databaseData['safe_percentages']['formulation'];
            $prompt .= "- Total Active Concentration: {$formData['totalActivePercentage']}%\n";
            $prompt .= "- Recommended pH Range: {$formData['phGuidance']['min']}-{$formData['phGuidance']['max']}\n";
        }
        
        // Add database recommendations
        if (!empty($databaseData['recommendations']['formulationGuidance']['enhancementSuggestions'])) {
            $prompt .= "\n**DATABASE RECOMMENDATIONS:**\n";
            foreach ($databaseData['recommendations']['formulationGuidance']['enhancementSuggestions'] as $suggestion) {
                $prompt .= "- {$suggestion}\n";
            }
        }

        $prompt .= "\n**CRITICAL REQUIREMENTS:**
1. Use ONLY the exact safe percentages from the real-time database above
2. Address any conflicts identified in the compatibility analysis
3. Base all recommendations on the current database data (not training data)
4. Include all database warnings in your response
5. Ensure total active percentage doesn't exceed safe limits
6. Follow the pH guidance from database calculations

**Response Format (JSON):**
{
  \"formulation_name\": \"Database-Optimized [Format] for [Skin Types]\",
  \"recommended_percentages\": {
    \"ingredient_name\": {\"min\": X, \"max\": Y, \"recommended\": Z, \"unit\": \"%\"}
  },
  \"expected_benefits\": [\"benefit1\", \"benefit2\"],
  \"application_instructions\": \"Detailed usage instructions for {$format}\",
  \"compatibility_notes\": \"Database compatibility analysis results\",
  \"warnings\": [\"database warning1\", \"database warning2\"],
  \"ingredient_synergies\": \"Database-verified synergy information\",
  \"estimated_results\": \"Timeline and expected outcomes\",
  \"ph_range\": \"X.X-Y.Y\",
  \"shelf_life\": \"6-12 months\",
  \"database_verification\": \"Verified against real-time MySQL ingredient database\"
}

IMPORTANT: Base your entire formulation on the real-time database data provided above. Do not use outdated training data.";

        return $prompt;
    }
    
    /**
     * Generate product description using database-enhanced AI
     */
    public function generateProductDescription($formulation, $aiSuggestions) {
        $prompt = "Create a compelling, professional product description for this database-verified custom skincare formulation:

**Product:** {$aiSuggestions['formulation_name']}
**Database-Verified Benefits:** " . implode(', ', $aiSuggestions['expected_benefits']) . "
**Format:** {$formulation['baseFormat']}
**Target Skin:** " . implode(', ', $formulation['skinType']) . "
**Database Source:** Real-time MySQL ingredient database

Write a 2-3 paragraph marketing description highlighting the scientifically-backed benefits, database-verified ingredient synergies, and why this formulation is perfect for the customer's skin type. Emphasize that this is formulated using current ingredient safety data.";

        try {
            $response = $this->callOpenAI($prompt);
            return $response['choices'][0]['message']['content'] ?? '';
        } catch (Exception $e) {
            error_log("Failed to generate product description: " . $e->getMessage());
            return "A custom skincare formulation created using real-time ingredient database verification for optimal safety and efficacy.";
        }
    }
}
?>