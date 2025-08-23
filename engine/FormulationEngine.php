<?php
/**
 * Smart Formulation Engine
 * Generates personalized skincare formulations based on user selections
 */

class FormulationEngine {
    
    private $formulationRules = [
        // Caffeine-based formulations
        'caffeine_beetroot_oat' => [
            'profile' => 'energizing + anti-fatigue + calming',
            'description' => 'A lightweight formula designed for energizing tired skin while providing gentle calming benefits.',
            'skin_types' => ['oily', 'combination', 'normal'],
            'formula' => [
                'caffeine' => 0.3,
                'beta-vulgaris' => 2.0,
                'avena-sativa' => 1.5
            ]
        ],
        'caffeine_bilberry_neem' => [
            'profile' => 'energizing + clarifying + purifying',
            'description' => 'Targeted formula for oily and acne-prone skin with energizing and purifying benefits.',
            'skin_types' => ['oily', 'combination'],
            'formula' => [
                'caffeine' => 0.3,
                'bilberry' => 1.5,
                'neem' => 1.0
            ]
        ],
        
        // Retinol-based formulations
        'retinol_hyaluronic' => [
            'profile' => 'anti-aging + hydrating',
            'description' => 'Advanced anti-aging formula with deep hydration for mature skin.',
            'skin_types' => ['normal', 'dry'],
            'formula' => [
                'retinol' => 0.1,
                'hyaluronic-acid' => 1.0
            ]
        ],
        
        // Vitamin C formulations
        'vitamin_c_brightening' => [
            'profile' => 'brightening + antioxidant protection',
            'description' => 'Powerful antioxidant formula for radiant, protected skin.',
            'skin_types' => ['all'],
            'formula' => [
                'vitamin-c' => 10.0
            ]
        ],
        
        // Niacinamide formulations
        'niacinamide_control' => [
            'profile' => 'pore-minimizing + oil control',
            'description' => 'Lightweight formula to minimize pores and control excess oil.',
            'skin_types' => ['oily', 'combination'],
            'formula' => [
                'niacinamide' => 5.0
            ]
        ]
    ];
    
    private $baseFormulations = [
        'mist' => [
            'water' => 85.0,
            'humectants' => 8.0,
            'preservative' => 0.5
        ],
        'serum' => [
            'water' => 70.0,
            'humectants' => 15.0,
            'emulsifiers' => 2.0,
            'preservative' => 0.5
        ],
        'cream' => [
            'water' => 60.0,
            'oils' => 20.0,
            'emulsifiers' => 5.0,
            'humectants' => 10.0,
            'preservative' => 0.5
        ]
    ];
    
    public function generateFormulation($skinTypes, $baseFormat, $keyActives, $extracts = [], $boosters = []) {
        // Find matching rule based on ingredients
        $ruleKey = $this->findMatchingRule($keyActives, $extracts);
        $rule = $this->formulationRules[$ruleKey] ?? null;
        
        // Generate base formulation
        $formulation = $this->createBaseFormulation($baseFormat);
        
        if ($rule) {
            // Apply rule-based formulation
            $formulation = $this->applyFormulationRule($formulation, $rule, $keyActives, $extracts, $boosters);
        } else {
            // Create custom formulation
            $formulation = $this->createCustomFormulation($formulation, $keyActives, $extracts, $boosters);
        }
        
        // Normalize to 100%
        $formulation = $this->normalizeFormulation($formulation);
        
        return [
            'title' => $this->generateTitle($baseFormat, $skinTypes, $keyActives),
            'description' => $rule['description'] ?? $this->generateDescription($keyActives, $extracts, $boosters),
            'profile' => $rule['profile'] ?? $this->generateProfile($keyActives),
            'formula' => $formulation,
            'skin_types' => $skinTypes,
            'base_format' => $baseFormat,
            'recommendations' => $this->generateRecommendations($keyActives)
        ];
    }
    
    private function findMatchingRule($keyActives, $extracts) {
        $ingredients = array_merge($keyActives, $extracts);
        
        // Check for specific combinations
        if (in_array('caffeine', $ingredients) && in_array('beta-vulgaris', $ingredients) && in_array('avena-sativa', $ingredients)) {
            return 'caffeine_beetroot_oat';
        }
        
        if (in_array('caffeine', $ingredients) && in_array('bilberry', $ingredients) && in_array('neem', $ingredients)) {
            return 'caffeine_bilberry_neem';
        }
        
        if (in_array('retinol', $ingredients) && in_array('hyaluronic-acid', $ingredients)) {
            return 'retinol_hyaluronic';
        }
        
        if (in_array('vitamin-c', $ingredients)) {
            return 'vitamin_c_brightening';
        }
        
        if (in_array('niacinamide', $ingredients)) {
            return 'niacinamide_control';
        }
        
        return null;
    }
    
    private function createBaseFormulation($baseFormat) {
        return $this->baseFormulations[$baseFormat] ?? $this->baseFormulations['mist'];
    }
    
    private function applyFormulationRule($formulation, $rule, $keyActives, $extracts, $boosters) {
        // Add active ingredients from rule
        foreach ($rule['formula'] as $ingredient => $percentage) {
            if (in_array($ingredient, $keyActives) || in_array($ingredient, $extracts)) {
                $formulation[$ingredient] = $percentage;
            }
        }
        
        // Add boosters
        foreach ($boosters as $booster) {
            $formulation[$booster] = $this->getBoosterPercentage($booster);
        }
        
        return $formulation;
    }
    
    private function createCustomFormulation($formulation, $keyActives, $extracts, $boosters) {
        // Add key actives with default percentages
        foreach ($keyActives as $active) {
            $formulation[$active] = $this->getActivePercentage($active);
        }
        
        // Add extracts
        foreach ($extracts as $extract) {
            $formulation[$extract] = $this->getExtractPercentage($extract);
        }
        
        // Add boosters
        foreach ($boosters as $booster) {
            $formulation[$booster] = $this->getBoosterPercentage($booster);
        }
        
        return $formulation;
    }
    
    private function getActivePercentage($active) {
        $percentages = [
            'caffeine' => 0.3,
            'l-carnitine' => 2.0,
            'retinol' => 0.1,
            'niacinamide' => 5.0,
            'vitamin-c' => 10.0,
            'hyaluronic-acid' => 1.0
        ];
        
        return $percentages[$active] ?? 1.0;
    }
    
    private function getExtractPercentage($extract) {
        $percentages = [
            'beta-vulgaris' => 2.0,
            'avena-sativa' => 1.5,
            'neem' => 1.0,
            'bilberry' => 1.5,
            'green-tea' => 2.0,
            'chamomile' => 1.5
        ];
        
        return $percentages[$extract] ?? 1.5;
    }
    
    private function getBoosterPercentage($booster) {
        $percentages = [
            'glycerin' => 5.0,
            'sodium-pca' => 3.0,
            'copper-peptides' => 0.5,
            'ceramides' => 3.0,
            'squalane' => 2.0
        ];
        
        return $percentages[$booster] ?? 2.0;
    }
    
    private function normalizeFormulation($formulation) {
        $total = array_sum($formulation);
        $normalized = [];
        
        foreach ($formulation as $ingredient => $percentage) {
            $normalized[$ingredient] = round(($percentage / $total) * 100, 2);
        }
        
        // Ensure total is exactly 100%
        $currentTotal = array_sum($normalized);
        if ($currentTotal != 100) {
            $largest = array_keys($normalized, max($normalized))[0];
            $normalized[$largest] += (100 - $currentTotal);
            $normalized[$largest] = round($normalized[$largest], 2);
        }
        
        return $normalized;
    }
    
    private function generateTitle($baseFormat, $skinTypes, $keyActives) {
        $skinTypeStr = implode(', ', array_map('ucfirst', $skinTypes));
        $activeStr = implode(' + ', array_map([$this, 'formatIngredientName'], $keyActives));
        
        return "Your {$activeStr} " . ucfirst($baseFormat) . " – {$skinTypeStr} Skin Formula";
    }
    
    private function generateDescription($keyActives, $extracts, $boosters) {
        $benefits = [];
        
        // Active benefits
        foreach ($keyActives as $active) {
            switch ($active) {
                case 'caffeine':
                    $benefits[] = 'energize and depuff';
                    break;
                case 'retinol':
                    $benefits[] = 'promote cell renewal';
                    break;
                case 'vitamin-c':
                    $benefits[] = 'brighten and protect';
                    break;
                case 'niacinamide':
                    $benefits[] = 'minimize pores';
                    break;
                case 'hyaluronic-acid':
                    $benefits[] = 'deeply hydrate';
                    break;
            }
        }
        
        // Extract benefits
        foreach ($extracts as $extract) {
            switch ($extract) {
                case 'beta-vulgaris':
                    $benefits[] = 'boost radiance';
                    break;
                case 'avena-sativa':
                    $benefits[] = 'soothe irritation';
                    break;
                case 'green-tea':
                    $benefits[] = 'provide antioxidant protection';
                    break;
            }
        }
        
        $benefitStr = implode(', ', array_slice($benefits, 0, 3));
        return "A carefully formulated blend designed to {$benefitStr}.";
    }
    
    private function generateProfile($keyActives) {
        $profiles = [];
        
        foreach ($keyActives as $active) {
            switch ($active) {
                case 'caffeine':
                    $profiles[] = 'energizing';
                    break;
                case 'retinol':
                    $profiles[] = 'anti-aging';
                    break;
                case 'vitamin-c':
                    $profiles[] = 'brightening';
                    break;
                case 'niacinamide':
                    $profiles[] = 'oil-control';
                    break;
            }
        }
        
        return implode(' + ', $profiles);
    }
    
    private function generateRecommendations($keyActives) {
        $recommendations = [];
        
        if (in_array('retinol', $keyActives)) {
            $recommendations[] = 'Use in evening only and always apply SPF during the day.';
            if (in_array('vitamin-c', $keyActives)) {
                $recommendations[] = 'Consider using Vitamin C in the morning and Retinol at night for best results.';
            }
        }
        
        if (in_array('vitamin-c', $keyActives)) {
            $recommendations[] = 'Store in a cool, dark place to maintain potency.';
        }
        
        return $recommendations;
    }
    
    private function formatIngredientName($ingredient) {
        return ucwords(str_replace('-', ' ', $ingredient));
    }
}
?>