<?php
/**
 * Helper functions for the skincare formulation app
 */

class FormulationHelpers {
    
    /**
     * Sanitize input data
     * @param mixed $data
     * @return mixed
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        if (is_string($data)) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
        
        return $data;
    }

    /**
     * Generate unique formulation ID
     * @return string
     */
    public static function generateFormulationId() {
        return 'FORM_' . strtoupper(uniqid());
    }

    /**
     * Format formulation for email
     * @param array $data
     * @return string
     */
    public static function formatFormulationForEmail($data) {
        $output = "New Skincare Formulation Request\n";
        $output .= "================================\n\n";
        
        $output .= "Customer: " . $data['contact']['name'] . "\n";
        $output .= "Email: " . $data['contact']['email'] . "\n\n";
        
        $output .= "Skin Type: " . implode(', ', $data['skinType']) . "\n";
        $output .= "Base Format: " . ucfirst($data['baseFormat']) . "\n\n";
        
        if (!empty($data['keyActives'])) {
            $output .= "Key Actives:\n";
            foreach ($data['keyActives'] as $active) {
                $output .= "- " . ucfirst($active) . "\n";
            }
            $output .= "\n";
        }
        
        if (!empty($data['extracts'])) {
            $output .= "Functional Extracts:\n";
            foreach ($data['extracts'] as $extract) {
                $output .= "- " . ucfirst($extract) . "\n";
            }
            $output .= "\n";
        }
        
        if (!empty($data['boosters'])) {
            $output .= "Boosters/Hydrators:\n";
            foreach ($data['boosters'] as $booster) {
                $output .= "- " . ucfirst($booster) . "\n";
            }
            $output .= "\n";
        }
        
        if (!empty($data['contact']['concerns'])) {
            $output .= "Additional Concerns: " . $data['contact']['concerns'] . "\n";
        }
        
        $output .= "\nSubmitted: " . date('Y-m-d H:i:s') . "\n";
        
        return $output;
    }

    /**
     * Log formulation submission
     * @param array $data
     * @param string $result
     */
    public static function logFormulationSubmission($data, $result) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'customer_email' => $data['contact']['email'] ?? 'unknown',
            'skin_types' => implode(',', $data['skinType'] ?? []),
            'base_format' => $data['baseFormat'] ?? 'unknown',
            'key_actives_count' => count($data['keyActives'] ?? []),
            'result' => $result
        ];
        
        error_log("Formulation Submission: " . json_encode($logEntry));
    }

    /**
     * Validate email format
     * @param string $email
     * @return bool
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
?>