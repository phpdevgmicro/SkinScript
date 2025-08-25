<?php
/**
 * Simple Formulation Controller
 * Handles form submissions - stores data and sends admin email
 */

require_once __DIR__ . '/../models/FormulationModel.php';
require_once __DIR__ . '/../services/EmailService.php';

class FormulationController {
    private $model;

    public function __construct() {
        $this->model = new FormulationModel();
        // Ensure table exists
        $this->model->createTableIfNotExists();
    }

    /**
     * Validate formulation data
     * @param array $data
     * @return array Validation result
     */
    private function validateFormulationData($data) {
        $errors = [];

        // Ensure all required arrays exist
        $data['skinType'] = $data['skinType'] ?? [];
        $data['keyActives'] = $data['keyActives'] ?? [];
        $data['contact'] = $data['contact'] ?? [];

        // Validate skin types
        if (empty($data['skinType']) || !is_array($data['skinType'])) {
            $errors[] = "At least one skin type must be selected";
        }

        // Validate base format
        $validFormats = ['mist', 'serum', 'cream'];
        if (empty($data['baseFormat']) || !in_array($data['baseFormat'], $validFormats)) {
            $errors[] = "Valid base format must be selected";
        }

        // Validate contact info
        if (empty($data['contact']['email']) || !filter_var($data['contact']['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email address is required";
        }

        // Get customer name from fullName field
        $customerName = $data['contact']['fullName'] ?? '';
        if (empty($customerName) || strlen(trim($customerName)) < 2) {
            $errors[] = "Name must be at least 2 characters long";
        }
        
        // Standardize the name field for processing
        $data['contact']['name'] = $customerName;

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data // Return cleaned data
        ];
    }

    /**
     * Generate simple formulation summary
     * @param array $data
     * @return string
     */
    private function generateFormulationSummary($data) {
        $summary = "Custom " . ucfirst($data['baseFormat']) . " for " . implode(', ', $data['skinType']) . " skin";
        
        if (!empty($data['keyActives'])) {
            $summary .= " with " . implode(', ', $data['keyActives']);
        }

        return $summary;
    }

    /**
     * Process formulation submission
     * @param array $data
     * @return array
     */
    public function submitFormulation($data) {
        // Validate data
        $validation = $this->validateFormulationData($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation['errors']
            ];
        }
        
        // Use cleaned data
        $data = $validation['data'];

        // Generate summary
        $summary = $this->generateFormulationSummary($data);

        // Save to database
        $formulationId = $this->model->saveFormulation($data);

        if ($formulationId) {
            // Add ID to data for email
            $data['id'] = $formulationId;
            
            // Send email notification to admin only
            $emailService = new EmailService();
            $emailSent = $emailService->sendAdminFormulationNotification($data);
            
            return [
                'success' => true,
                'message' => 'Formulation request submitted successfully',
                'formulation_id' => $formulationId,
                'summary' => $summary,
                'email_sent' => $emailSent
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to save formulation request'
            ];
        }
    }

}
?>