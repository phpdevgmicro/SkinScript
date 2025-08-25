<?php
/**
 * Formulation Controller
 * Handles business logic for skincare formulations
 */

require_once __DIR__ . '/../models/FormulationModel.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../services/PDFService.php';
require_once __DIR__ . '/../services/FormulationTemplateService.php';

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
        $data['extracts'] = $data['extracts'] ?? [];
        $data['boosters'] = $data['boosters'] ?? [];
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

        // Validate key actives (max 3)
        if (count($data['keyActives']) > 3) {
            $errors[] = "Maximum 3 key actives allowed";
        }

        // Validate contact info
        if (empty($data['contact']['email']) || !filter_var($data['contact']['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email address is required";
        }

        // Handle both 'name' and 'fullName' field names
        $customerName = $data['contact']['name'] ?? $data['contact']['fullName'] ?? '';
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
     * Check for ingredient incompatibilities
     * @param array $keyActives
     * @return array
     */
    private function checkIncompatibilities($keyActives) {
        $incompatibleCombinations = [
            ['retinol', 'vitamin-c'],
            ['retinol', 'niacinamide'],
            ['vitamin-c', 'niacinamide']
        ];

        $warnings = [];
        
        foreach ($incompatibleCombinations as $combo) {
            if (in_array($combo[0], $keyActives) && in_array($combo[1], $keyActives)) {
                $warnings[] = "Warning: " . ucfirst($combo[0]) . " and " . ucfirst($combo[1]) . " may not be compatible";
            }
        }

        return $warnings;
    }

    /**
     * Generate formulation summary
     * @param array $data
     * @return string
     */
    private function generateFormulationSummary($data) {
        $summary = "Custom " . ucfirst($data['baseFormat']) . " for " . implode(', ', $data['skinType']) . " skin";
        
        if (!empty($data['keyActives'])) {
            $summary .= " with " . implode(', ', $data['keyActives']);
        }

        if (!empty($data['extracts'])) {
            $summary .= " featuring " . implode(', ', $data['extracts']) . " extracts";
        }

        if (!empty($data['boosters'])) {
            $summary .= " plus " . implode(', ', $data['boosters']) . " boosters";
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

        // Check for incompatibilities
        $warnings = $this->checkIncompatibilities($data['keyActives']);

        // Generate summary
        $summary = $this->generateFormulationSummary($data);

        // Save to database
        $formulationId = $this->model->saveFormulation($data);

        if ($formulationId) {
            // Add ID to data for email/PDF generation
            $data['id'] = $formulationId;
            
            // Send email notifications
            $emailService = new EmailService();
            $emailSent = $emailService->sendFormulationNotification($data);
            
            // Generate formulation suggestions
            $templateService = new FormulationTemplateService();
            $suggestions = $templateService->generateFormulationSuggestions($data);
            
            // Generate PDF report
            $pdfService = new PDFService();
            $pdfResult = $pdfService->generateFormulationPDF($data, $suggestions);
            
            return [
                'success' => true,
                'message' => 'Formulation saved successfully',
                'formulation_id' => $formulationId,
                'summary' => $summary,
                'warnings' => $warnings,
                'suggestions' => $suggestions,
                'email_sent' => $emailSent,
                'pdf_generated' => ($pdfResult && isset($pdfResult['success']) ? $pdfResult['success'] : $pdfResult !== false),
                'pdf_info' => $pdfResult
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to save formulation'
            ];
        }
    }

    /**
     * Get formulation details
     * @param int $id
     * @return array
     */
    public function getFormulation($id) {
        $formulation = $this->model->getFormulationById($id);
        
        if ($formulation) {
            return [
                'success' => true,
                'formulation' => $formulation
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Formulation not found'
            ];
        }
    }

    /**
     * Get all formulations
     * @param int $page
     * @return array
     */
    public function getAllFormulations($page = 1) {
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $formulations = $this->model->getAllFormulations($limit, $offset);
        
        if ($formulations !== false) {
            return [
                'success' => true,
                'formulations' => $formulations,
                'page' => $page,
                'limit' => $limit
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to retrieve formulations'
            ];
        }
    }
}
?>