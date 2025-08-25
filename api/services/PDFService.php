<?php
/**
 * PDF Generation Service for formulation reports
 */

require_once __DIR__ . '/../../vendor/autoload.php';

// TCPDF class is available via autoloader

class PDFService {
    
    /**
     * Generate PDF formulation report
     * @param array $formulation
     * @param array $suggestions
     * @return string|false PDF content or false on failure
     */
    public function generateFormulationPDF($formulation, $suggestions = []) {
        try {
            // Create new PDF document using TCPDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Set document information
            $pdf->setCreator('Skincare Formulation App');
            $pdf->setAuthor('Skincare Formulation App');
            $pdf->setTitle('Custom Skincare Formulation Report');
            $pdf->setSubject('Personalized Skincare Formulation');
            
            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Set margins
            $pdf->setMargins(20, 20, 20);
            $pdf->setAutoPageBreak(true, 20);
            
            // Add a page
            $pdf->AddPage();
            
            // Generate content
            $html = $this->generateFormulationHTML($formulation, $suggestions);
            
            // Write HTML content
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Generate filename
            $filename = 'formulation_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdfDir = __DIR__ . '/../../pdf';
            if (!file_exists($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }
            $filepath = $pdfDir . '/' . $filename;
            
            // Output PDF to file
            $pdf->Output($filepath, 'F');
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'type' => 'application/pdf',
                'content' => base64_encode(file_get_contents($filepath))
            ];
            
        } catch (Exception $e) {
            error_log("PDF generation failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'content' => $this->generateFormulationHTML($formulation, $suggestions), // Fallback to HTML
                'filename' => 'formulation_' . date('Y-m-d_H-i-s') . '.html',
                'filepath' => '/pdf/formulation_' . date('Y-m-d_H-i-s') . '.html',
                'type' => 'text/html'
            ];
        }
    }
    
    private function generateFormulationHTML($formulation, $suggestions = []) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Custom Skincare Formulation Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { background: #f8f9fa; padding: 20px; border-left: 4px solid #007bff; margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        .section h3 { color: #007bff; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .ingredient-list { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .ingredient-item { margin: 5px 0; padding: 5px; background: white; border-radius: 3px; }
        .summary { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; }
    </style>
</head>
<body>';

        $html .= '<div class="header">';
        $html .= '<h1>Custom Skincare Formulation Report</h1>';
        $customerName = $formulation['contact']['name'] ?? $formulation['contact']['fullName'] ?? 'Customer';
        $html .= '<p><strong>Customer:</strong> ' . htmlspecialchars($customerName) . '</p>';
        $html .= '<p><strong>Date:</strong> ' . date('F j, Y') . '</p>';
        $html .= '<p><strong>Formulation ID:</strong> FORM-' . sprintf('%06d', $formulation['id'] ?? rand(100000, 999999)) . '</p>';
        $html .= '</div>';

        $html .= '<div class="summary">';
        $html .= '<h2>Formulation Summary</h2>';
        $html .= '<p><strong>Base Format:</strong> ' . ucfirst($formulation['baseFormat']) . '</p>';
        $html .= '<p><strong>Skin Type:</strong> ' . implode(', ', $formulation['skinType']) . '</p>';
        $html .= '</div>';

        if (!empty($formulation['keyActives'])) {
            $html .= '<div class="section">';
            $html .= '<h3>Key Active Ingredients</h3>';
            $html .= '<div class="ingredient-list">';
            foreach ($formulation['keyActives'] as $active) {
                $html .= '<div class="ingredient-item">• ' . ucfirst($active) . '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }

        if (!empty($formulation['extracts'])) {
            $html .= '<div class="section">';
            $html .= '<h3>Functional Extracts</h3>';
            $html .= '<div class="ingredient-list">';
            foreach ($formulation['extracts'] as $extract) {
                $html .= '<div class="ingredient-item">• ' . ucfirst($extract) . '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }

        if (!empty($formulation['boosters'])) {
            $html .= '<div class="section">';
            $html .= '<h3>Boosters & Hydrators</h3>';
            $html .= '<div class="ingredient-list">';
            foreach ($formulation['boosters'] as $booster) {
                $html .= '<div class="ingredient-item">• ' . ucfirst($booster) . '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }

        // Add AI-generated formulation content if available
        if (!empty($suggestions['ai_formulation'])) {
            $aiFormulation = $suggestions['ai_formulation'];
            
            $html .= '<div class="section">';
            $html .= '<h3>🤖 AI-Generated Formulation Analysis</h3>';
            $html .= '<div class="summary">';
            $html .= '<h4>' . htmlspecialchars($aiFormulation['formulation_name'] ?? 'Custom AI Formulation') . '</h4>';
            
            if (!empty($aiFormulation['recommended_percentages'])) {
                $html .= '<div class="ingredient-list">';
                foreach ($aiFormulation['recommended_percentages'] as $ingredient => $range) {
                    $html .= '<div class="ingredient-item">';
                    $html .= '<strong>' . ucfirst($ingredient) . ':</strong> ';
                    $html .= ($range['recommended'] ?? 1.0) . '% (Safe range: ' . ($range['min'] ?? 0.1) . '% - ' . ($range['max'] ?? 5.0) . '%)';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            
            if (!empty($aiFormulation['expected_benefits'])) {
                $html .= '<p><strong>Expected Benefits:</strong> ' . implode(', ', $aiFormulation['expected_benefits']) . '</p>';
            }
            
            if (!empty($aiFormulation['application_instructions'])) {
                $html .= '<p><strong>Application Instructions:</strong> ' . htmlspecialchars($aiFormulation['application_instructions']) . '</p>';
            }
            
            if (!empty($aiFormulation['ingredient_synergies'])) {
                $html .= '<p><strong>Ingredient Synergies:</strong> ' . htmlspecialchars($aiFormulation['ingredient_synergies']) . '</p>';
            }
            
            if (!empty($aiFormulation['warnings']) && is_array($aiFormulation['warnings'])) {
                $html .= '<p><strong>Warnings:</strong> ' . implode(', ', $aiFormulation['warnings']) . '</p>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
        }

        // Add product description if available
        if (!empty($suggestions['product_description'])) {
            $html .= '<div class="section">';
            $html .= '<h3>Product Description</h3>';
            $html .= '<p>' . htmlspecialchars($suggestions['product_description']) . '</p>';
            $html .= '</div>';
        }

        // Add template-based formulation suggestions if available
        if (!empty($suggestions)) {
            if (!empty($suggestions['recommended_percentages'])) {
                $html .= '<div class="section">';
                $html .= '<h3>Recommended Concentrations</h3>';
                $html .= '<div class="ingredient-list">';
                foreach ($suggestions['recommended_percentages'] as $ingredient => $range) {
                    $html .= '<div class="ingredient-item">';
                    $html .= '<strong>' . ucfirst($ingredient) . ':</strong> ';
                    $html .= $range['recommended'] . '% (Safe range: ' . $range['min'] . '% - ' . $range['max'] . '%)';
                    $html .= '</div>';
                }
                $html .= '</div>';
                $html .= '</div>';
            }
            
            if (!empty($suggestions['formulation_benefits'])) {
                $html .= '<div class="section">';
                $html .= '<h3>Expected Benefits</h3>';
                $html .= '<div class="ingredient-list">';
                foreach ($suggestions['formulation_benefits'] as $benefit) {
                    $html .= '<div class="ingredient-item">• ' . $benefit . '</div>';
                }
                $html .= '</div>';
                $html .= '</div>';
            }
            
            if (!empty($suggestions['application_instructions'])) {
                $html .= '<div class="section">';
                $html .= '<h3>Usage Instructions</h3>';
                $html .= '<p>' . $suggestions['application_instructions'] . '</p>';
                $html .= '</div>';
            }
            
            if (!empty($suggestions['ingredient_synergies'])) {
                $html .= '<div class="section">';
                $html .= '<h3>Ingredient Synergies</h3>';
                $html .= '<div class="ingredient-list">';
                foreach ($suggestions['ingredient_synergies'] as $synergy) {
                    $html .= '<div class="ingredient-item">• ' . $synergy . '</div>';
                }
                $html .= '</div>';
                $html .= '</div>';
            }
        } else {
            $html .= '<div class="section">';
            $html .= '<h3>Usage Instructions</h3>';
            $html .= '<p>Apply as directed. Start with a patch test. Use consistently for best results.</p>';
            $html .= '</div>';
        }

        $html .= '<div class="footer">';
        $html .= '<p>This formulation was created based on your specific skin needs and preferences.</p>';
        $html .= '<p>Generated by Skincare Formulation App</p>';
        $html .= '</div>';

        $html .= '</body></html>';
        
        return $html;
    }
    
    
    /**
     * Save PDF to file system
     * @param string $content
     * @param string $filename
     * @return string|false File path or false on failure
     */
    public function savePDFToFile($content, $filename = null) {
        if (!$filename) {
            $filename = 'formulation_' . date('Y-m-d_H-i-s') . '.pdf';
        }
        
        $directory = __DIR__ . '/../storage/pdfs/';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $filepath = $directory . $filename;
        
        if (file_put_contents($filepath, $content)) {
            return $filepath;
        }
        
        return false;
    }
}
?>