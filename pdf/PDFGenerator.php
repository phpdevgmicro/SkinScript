<?php
/**
 * PDF Generator for Skincare Formulations
 * Creates professional PDF documents with formulation details
 */

class PDFGenerator {
    
    public function generateFormulationPDF($formulation, $contactInfo) {
        $html = $this->createFormulationHTML($formulation, $contactInfo);
        $filename = $this->generateFilename($formulation['formulation_id']);
        
        // Create PDF directory if it doesn't exist
        $pdfDir = __DIR__ . '/../generated_pdfs/';
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }
        
        $filepath = $pdfDir . $filename;
        
        // Try to generate PDF using wkhtmltopdf
        if ($this->generateWithWkhtmltopdf($html, $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'download_url' => '/download_pdf.php?file=' . urlencode($filename)
            ];
        } else {
            // Fallback: save as HTML
            $htmlFile = str_replace('.pdf', '.html', $filepath);
            file_put_contents($htmlFile, $html);
            
            return [
                'success' => true,
                'filename' => str_replace('.pdf', '.html', $filename),
                'filepath' => $htmlFile,
                'download_url' => '/download_pdf.php?file=' . urlencode(str_replace('.pdf', '.html', $filename)),
                'fallback' => true
            ];
        }
    }
    
    private function generateWithWkhtmltopdf($html, $filepath) {
        // Create temporary HTML file
        $tempHtml = tempnam(sys_get_temp_dir(), 'formulation_') . '.html';
        file_put_contents($tempHtml, $html);
        
        // Try to execute wkhtmltopdf
        $command = "wkhtmltopdf --page-size A4 --margin-top 0.5in --margin-bottom 0.5in --margin-left 0.5in --margin-right 0.5in '$tempHtml' '$filepath' 2>/dev/null";
        
        exec($command, $output, $returnCode);
        
        // Clean up temp file
        unlink($tempHtml);
        
        return $returnCode === 0 && file_exists($filepath);
    }
    
    private function createFormulationHTML($formulation, $contactInfo) {
        $ingredientRows = '';
        foreach ($formulation['formula'] as $ingredient => $percentage) {
            $ingredientName = $this->formatIngredientName($ingredient);
            $ingredientRows .= "<tr><td>{$ingredientName}</td><td>{$percentage}%</td></tr>";
        }
        
        $recommendationsHtml = '';
        if (!empty($formulation['recommendations'])) {
            $recommendationsHtml = '<div class="recommendations">
                <h3>Usage Recommendations:</h3>
                <ul>';
            foreach ($formulation['recommendations'] as $rec) {
                $recommendationsHtml .= "<li>{$rec}</li>";
            }
            $recommendationsHtml .= '</ul></div>';
        }
        
        $skinTypesStr = implode(', ', array_map('ucfirst', $formulation['skin_types']));
        $currentDate = date('F j, Y');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>{$formulation['title']}</title>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    background: #fff;
                }
                .header {
                    text-align: center;
                    border-bottom: 3px solid #667eea;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .title {
                    color: #667eea;
                    font-size: 28px;
                    font-weight: bold;
                    margin: 0;
                }
                .subtitle {
                    color: #666;
                    font-size: 16px;
                    margin: 10px 0 0 0;
                }
                .profile-badge {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 8px 16px;
                    border-radius: 20px;
                    display: inline-block;
                    font-size: 14px;
                    font-weight: bold;
                    margin: 15px 0;
                }
                .description {
                    background: #f8f9ff;
                    padding: 20px;
                    border-radius: 8px;
                    border-left: 4px solid #667eea;
                    margin: 20px 0;
                    font-size: 16px;
                }
                .formula-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                    background: white;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                }
                .formula-table th {
                    background: #667eea;
                    color: white;
                    padding: 12px;
                    text-align: left;
                    font-weight: bold;
                }
                .formula-table td {
                    padding: 10px 12px;
                    border-bottom: 1px solid #eee;
                }
                .formula-table tr:nth-child(even) {
                    background: #f8f9ff;
                }
                .contact-info {
                    background: #f0f4ff;
                    padding: 15px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                .recommendations {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    padding: 15px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                .recommendations h3 {
                    color: #856404;
                    margin-top: 0;
                }
                .recommendations ul {
                    margin: 10px 0;
                    padding-left: 20px;
                }
                .footer {
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                }
                .meta-info {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin: 20px 0;
                    font-size: 14px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1 class='title'>{$formulation['title']}</h1>
                <p class='subtitle'>Personalized Skincare Formulation</p>
                <div class='profile-badge'>{$formulation['profile']}</div>
            </div>
            
            <div class='meta-info'>
                <div><strong>Skin Type:</strong> {$skinTypesStr}</div>
                <div><strong>Format:</strong> " . ucfirst($formulation['base_format']) . "</div>
                <div><strong>Generated:</strong> {$currentDate}</div>
            </div>
            
            <div class='description'>
                {$formulation['description']}
            </div>
            
            <h2>Formulation Breakdown (% w/w)</h2>
            <table class='formula-table'>
                <thead>
                    <tr>
                        <th>Ingredient</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    {$ingredientRows}
                </tbody>
            </table>
            
            <div class='contact-info'>
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> {$contactInfo['fullName']}</p>
                <p><strong>Email:</strong> {$contactInfo['email']}</p>
                " . (!empty($contactInfo['skinConcerns']) ? "<p><strong>Skin Concerns:</strong> {$contactInfo['skinConcerns']}</p>" : "") . "
            </div>
            
            {$recommendationsHtml}
            
            <div class='footer'>
                <p>This formulation was generated based on your selections. For best results, consult with a skincare professional.</p>
                <p>Formulation ID: {$formulation['formulation_id']} | Generated on {$currentDate}</p>
            </div>
        </body>
        </html>";
    }
    
    private function formatIngredientName($ingredient) {
        // Handle special formatting
        $formatted = str_replace('-', ' ', $ingredient);
        $formatted = ucwords($formatted);
        
        // Special cases
        $replacements = [
            'Pca' => 'PCA',
            'Hyaluronic Acid' => 'Hyaluronic Acid',
            'L Carnitine' => 'L-Carnitine',
            'Beta Vulgaris' => 'Beta Vulgaris (Beet Root)',
            'Avena Sativa' => 'Avena Sativa (Oat)',
            'Green Tea' => 'Green Tea Extract'
        ];
        
        foreach ($replacements as $search => $replace) {
            $formatted = str_replace($search, $replace, $formatted);
        }
        
        return $formatted;
    }
    
    private function generateFilename($formulationId) {
        return "formulation_{$formulationId}_" . date('Y-m-d') . '.pdf';
    }
}
?>