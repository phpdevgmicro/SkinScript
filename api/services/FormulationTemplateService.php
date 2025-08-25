<?php
/**
 * Formulation Template Service
 * Handles pre-coded template logic and formulation suggestions
 */

class FormulationTemplateService {
    
    private $ingredientData;
    private $compatibilityRules;
    
    public function __construct() {
        $this->initializeIngredientData();
        $this->initializeCompatibilityRules();
    }
    
    /**
     * Generate formulation suggestions based on selections
     * @param array $formulation
     * @return array
     */
    public function generateFormulationSuggestions($formulation) {
        $suggestions = [
            'recommended_percentages' => $this->calculateSafeRanges($formulation),
            'formulation_benefits' => $this->analyzeFormulationBenefits($formulation),
            'application_instructions' => $this->generateApplicationInstructions($formulation),
            'compatibility_warnings' => $this->checkCompatibility($formulation),
            'ingredient_synergies' => $this->findIngredientSynergies($formulation)
        ];
        
        return $suggestions;
    }
    
    /**
     * Calculate safe percentage ranges for selected ingredients
     * @param array $formulation
     * @return array
     */
    private function calculateSafeRanges($formulation) {
        $ranges = [];
        
        // Key actives safe ranges
        foreach ($formulation['keyActives'] as $active) {
            switch (strtolower($active)) {
                case 'caffeine':
                    $ranges[$active] = ['min' => 0.5, 'max' => 3.0, 'recommended' => 1.0];
                    break;
                case 'retinol':
                    $ranges[$active] = ['min' => 0.1, 'max' => 1.0, 'recommended' => 0.3];
                    break;
                case 'niacinamide':
                    $ranges[$active] = ['min' => 2.0, 'max' => 10.0, 'recommended' => 5.0];
                    break;
                case 'vitamin-c':
                    $ranges[$active] = ['min' => 5.0, 'max' => 20.0, 'recommended' => 10.0];
                    break;
                case 'salicylic-acid':
                    $ranges[$active] = ['min' => 0.5, 'max' => 2.0, 'recommended' => 1.0];
                    break;
                case 'hyaluronic-acid':
                    $ranges[$active] = ['min' => 0.1, 'max' => 2.0, 'recommended' => 1.0];
                    break;
                default:
                    $ranges[$active] = ['min' => 0.1, 'max' => 5.0, 'recommended' => 1.0];
                    break;
            }
        }
        
        // Extracts typically used at lower concentrations
        foreach ($formulation['extracts'] as $extract) {
            $ranges[$extract] = ['min' => 0.1, 'max' => 2.0, 'recommended' => 0.5];
        }
        
        // Boosters/hydrators
        foreach ($formulation['boosters'] as $booster) {
            switch (strtolower($booster)) {
                case 'glycerin':
                    $ranges[$booster] = ['min' => 2.0, 'max' => 10.0, 'recommended' => 5.0];
                    break;
                case 'sodium-pca':
                    $ranges[$booster] = ['min' => 0.5, 'max' => 3.0, 'recommended' => 1.5];
                    break;
                default:
                    $ranges[$booster] = ['min' => 0.5, 'max' => 5.0, 'recommended' => 2.0];
                    break;
            }
        }
        
        return $ranges;
    }
    
    /**
     * Analyze formulation benefits based on ingredient combinations
     * @param array $formulation
     * @return array
     */
    private function analyzeFormulationBenefits($formulation) {
        $benefits = [];
        $skinTypes = $formulation['skinType'];
        $actives = $formulation['keyActives'];
        $extracts = $formulation['extracts'];
        
        // Analyze based on skin type
        if (in_array('oily', $skinTypes)) {
            $benefits[] = "Oil control and pore refinement";
            if (in_array('salicylic-acid', $actives)) {
                $benefits[] = "Enhanced sebum regulation and gentle exfoliation";
            }
        }
        
        if (in_array('dry', $skinTypes)) {
            $benefits[] = "Deep hydration and moisture barrier repair";
            if (in_array('hyaluronic-acid', $actives)) {
                $benefits[] = "Superior moisture retention";
            }
        }
        
        if (in_array('sensitive', $skinTypes)) {
            $benefits[] = "Gentle soothing and irritation reduction";
        }
        
        // Analyze active combinations
        if (in_array('caffeine', $actives)) {
            $benefits[] = "Energizing and circulation-boosting effects";
            $benefits[] = "Antioxidant protection";
        }
        
        if (in_array('niacinamide', $actives)) {
            $benefits[] = "Improved skin texture and tone evenness";
            $benefits[] = "Pore appearance minimization";
        }
        
        // Extract benefits
        if (in_array('beta-vulgaris', $extracts)) {
            $benefits[] = "Natural antioxidant boost from beetroot extract";
        }
        
        if (in_array('avena-sativa', $extracts)) {
            $benefits[] = "Soothing and anti-inflammatory properties from oat extract";
        }
        
        return array_unique($benefits);
    }
    
    /**
     * Generate application instructions based on formulation
     * @param array $formulation
     * @return string
     */
    private function generateApplicationInstructions($formulation) {
        $format = $formulation['baseFormat'];
        $instructions = "";
        
        switch ($format) {
            case 'mist':
                $instructions = "Shake well before use. Hold 6-8 inches from face and spray evenly. ";
                $instructions .= "Use morning and evening after cleansing. Allow to absorb before applying moisturizer.";
                break;
            case 'serum':
                $instructions = "Apply 2-3 drops to clean skin. Gently pat and press into skin. ";
                $instructions .= "Use morning and/or evening before moisturizer.";
                break;
            case 'cream':
                $instructions = "Apply a small amount to clean skin using gentle upward motions. ";
                $instructions .= "Use as final step in your routine, morning and evening.";
                break;
        }
        
        // Add specific warnings for certain actives
        if (in_array('retinol', $formulation['keyActives'])) {
            $instructions .= " Start with every other night and gradually increase frequency. Always use sunscreen during the day.";
        }
        
        if (in_array('salicylic-acid', $formulation['keyActives'])) {
            $instructions .= " May increase sun sensitivity - use sunscreen daily.";
        }
        
        return $instructions;
    }
    
    /**
     * Check ingredient compatibility
     * @param array $formulation
     * @return array
     */
    private function checkCompatibility($formulation) {
        $warnings = [];
        $actives = $formulation['keyActives'];
        
        // Check incompatible combinations
        if (in_array('retinol', $actives) && in_array('vitamin-c', $actives)) {
            $warnings[] = "Retinol and Vitamin C may cause irritation when used together. Consider alternating application times.";
        }
        
        if (in_array('retinol', $actives) && in_array('salicylic-acid', $actives)) {
            $warnings[] = "Retinol and Salicylic Acid combination may be too strong for sensitive skin. Start slowly.";
        }
        
        if (count($actives) > 2) {
            $warnings[] = "Multiple active ingredients detected. Patch test recommended before full application.";
        }
        
        return $warnings;
    }
    
    /**
     * Find beneficial ingredient synergies
     * @param array $formulation
     * @return array
     */
    private function findIngredientSynergies($formulation) {
        $synergies = [];
        $actives = $formulation['keyActives'];
        $boosters = $formulation['boosters'];
        
        // Beneficial combinations
        if (in_array('niacinamide', $actives) && in_array('hyaluronic-acid', $actives)) {
            $synergies[] = "Niacinamide + Hyaluronic Acid: Enhanced hydration and barrier function";
        }
        
        if (in_array('caffeine', $actives) && in_array('glycerin', $boosters)) {
            $synergies[] = "Caffeine + Glycerin: Energizing effect with moisture retention";
        }
        
        if (in_array('vitamin-c', $actives) && in_array('niacinamide', $actives)) {
            $synergies[] = "Vitamin C + Niacinamide: Powerful antioxidant and brightening combination";
        }
        
        return $synergies;
    }
    
    private function initializeIngredientData() {
        // Initialize ingredient database with properties
        $this->ingredientData = [
            'caffeine' => ['type' => 'active', 'benefits' => ['energizing', 'antioxidant'], 'strength' => 'medium'],
            'retinol' => ['type' => 'active', 'benefits' => ['anti-aging', 'renewal'], 'strength' => 'high'],
            'niacinamide' => ['type' => 'active', 'benefits' => ['pore-minimizing', 'brightening'], 'strength' => 'low'],
            // Add more as needed
        ];
    }
    
    private function initializeCompatibilityRules() {
        $this->compatibilityRules = [
            'incompatible' => [
                ['retinol', 'vitamin-c'],
                ['retinol', 'salicylic-acid']
            ],
            'synergistic' => [
                ['niacinamide', 'hyaluronic-acid'],
                ['vitamin-c', 'vitamin-e']
            ]
        ];
    }
}
?>