<?php
/**
 * Email Service for sending formulation notifications
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $fromEmail;
    private $fromName;
    private $smtpConfig;
    
    public function __construct() {
        $this->fromEmail = 'phpdevgmicro@gmail.com';
        $this->fromName = 'Skincare Formulation App';
        
        // SMTP Configuration - you can update these with your SMTP settings
        $this->smtpConfig = [
            'host' => 'smtp-relay.sendinblue.com',
            'port' =>  587,
            'username' => 'phpdevgmicro@gmail.com',
            'password' => 'N2DFZECX67YGBHRO',
            'encryption' => 'tls'
        ];
    }

    
    /**
     * Send admin-only formulation notification email
     * @param array $formulation
     * @return bool
     */
    public function sendAdminFormulationNotification($formulation) {
        $customerName = $formulation['contact']['name'] ?? $formulation['contact']['fullName'] ?? 'Customer';
        
        // Get admin email from settings
        $adminEmail = $this->getAdminEmail();
        $adminSubject = "New Skincare Formulation Request from " . $customerName;
        $adminMessage = $this->generateAdminEmail($formulation);
        
        return $this->sendEmail($adminEmail, $adminSubject, $adminMessage);
    }

    /**
     * Send formulation notification email
     * @param array $formulation
     * @return bool
     */
    public function sendFormulationNotification($formulation) {
        $customerEmail = $formulation['contact']['email'];
        $customerName = $formulation['contact']['name'] ?? $formulation['contact']['fullName'] ?? 'Customer';
        
        // Email to customer
        $customerSubject = "Your Custom Skincare Formulation Request";
        $customerMessage = $this->generateCustomerEmail($formulation);
        $customerSent = $this->sendEmail($customerEmail, $customerSubject, $customerMessage);
        
        // Get admin email from settings
        $adminEmail = $this->getAdminEmail();
        $adminSubject = "New Skincare Formulation Request from " . $customerName;
        $adminMessage = $this->generateAdminEmail($formulation);
        $adminSent = $this->sendEmail($adminEmail, $adminSubject, $adminMessage);
        
        return $customerSent && $adminSent;
    }
    
    /**
     * Get admin email from settings
     * @return string
     */
    private function getAdminEmail() {
        try {
            require_once __DIR__ . '/../models/AdminUserModel.php';
            $adminModel = new AdminUserModel();
            $adminEmail = $adminModel->getSetting('admin_notification_email');
            return $adminEmail ?: 'admin@skincraft.com'; // fallback
        } catch (Exception $e) {
            error_log("Failed to get admin email: " . $e->getMessage());
            return 'admin@skincraft.com'; // fallback
        }
    }
    
    private function generateCustomerEmail($formulation) {
        $customerName = $formulation['contact']['name'] ?? $formulation['contact']['fullName'] ?? 'Customer';
        $message = "Dear " . htmlspecialchars($customerName) . ",\n\n";
        $message .= "Thank you for your custom skincare formulation request!\n\n";
        $message .= "Here's a summary of your request:\n\n";
        
        $message .= "Skin Type: " . implode(', ', $formulation['skinType']) . "\n";
        $message .= "Base Format: " . ucfirst($formulation['baseFormat']) . "\n\n";
        
        if (!empty($formulation['keyActives'])) {
            $message .= "Key Active Ingredients:\n";
            foreach ($formulation['keyActives'] as $active) {
                $message .= "- " . ucfirst($active) . "\n";
            }
            $message .= "\n";
        }
        
        if (!empty($formulation['extracts'])) {
            $message .= "Functional Extracts:\n";
            foreach ($formulation['extracts'] as $extract) {
                $message .= "- " . ucfirst($extract) . "\n";
            }
            $message .= "\n";
        }
        
        if (!empty($formulation['boosters'])) {
            $message .= "Boosters/Hydrators:\n";
            foreach ($formulation['boosters'] as $booster) {
                $message .= "- " . ucfirst($booster) . "\n";
            }
            $message .= "\n";
        }
        
        $message .= "We'll review your request and get back to you within 24-48 hours with your custom formulation.\n\n";
        $message .= "Best regards,\n";
        $message .= "The Skincare Formulation Team";
        
        return $message;
    }
    
    private function generateAdminEmail($formulation) {
        $customerName = $formulation['contact']['name'] ?? $formulation['contact']['fullName'] ?? 'Customer';
        $customerEmail = htmlspecialchars($formulation['contact']['email']);
        
        $message = '<div class="section customer-info">';
        $message .= '<h3>👤 Customer Information</h3>';
        $message .= '<p><strong>Name:</strong> ' . htmlspecialchars($customerName) . '</p>';
        $message .= '<p><strong>Email:</strong> ' . $customerEmail . '</p>';
        $message .= '<p><strong>Submission Date:</strong> ' . date('Y-m-d H:i:s T') . '</p>';
        $message .= '<p><strong>Formulation ID:</strong> ' . ($formulation['id'] ?? 'N/A') . '</p>';
        $message .= '</div>';
        
        $message .= '<div class="section skin-profile">';
        $message .= '<h3>🔬 Skin Profile</h3>';
        $message .= '<p><strong>Skin Types:</strong> ' . $this->formatSelections($formulation['skinType']) . '</p>';
        $message .= '<p><strong>Preferred Format:</strong> ' . ucfirst($formulation['baseFormat']) . ' (spray/serum/cream)</p>';
        $message .= '</div>';
        
        if (!empty($formulation['keyActives'])) {
            $message .= '<div class="section actives">';
            $message .= '<h3>⚗️ Key Active Ingredients</h3>';
            $message .= '<ul class="ingredient-list">';
            foreach ($formulation['keyActives'] as $active) {
                $message .= '<li>' . $this->formatIngredientName($active) . '</li>';
            }
            $message .= '</ul>';
            $message .= '</div>';
        }
        
        if (!empty($formulation['extracts'])) {
            $message .= '<div class="section extracts">';
            $message .= '<h3>🌿 Botanical Extracts</h3>';
            $message .= '<ul class="ingredient-list">';
            foreach ($formulation['extracts'] as $extract) {
                $message .= '<li>' . $this->formatIngredientName($extract) . '</li>';
            }
            $message .= '</ul>';
            $message .= '</div>';
        }
        
        if (!empty($formulation['boosters'])) {
            $message .= '<div class="section hydrators">';
            $message .= '<h3>💧 Hydrators & Boosters</h3>';
            $message .= '<ul class="ingredient-list">';
            foreach ($formulation['boosters'] as $booster) {
                $message .= '<li>' . $this->formatIngredientName($booster) . '</li>';
            }
            $message .= '</ul>';
            $message .= '</div>';
        }
        
        $concerns = $formulation['contact']['concerns'] ?? $formulation['contact']['skinConcerns'] ?? '';
        if (!empty($concerns)) {
            $message .= '<div class="section">';
            $message .= '<h3>📝 Special Requests & Concerns</h3>';
            $message .= '<p>' . htmlspecialchars($concerns) . '</p>';
            $message .= '</div>';
        }
        
        $message .= '<div class="summary-box">';
        $message .= '<h3>📊 Formulation Summary</h3>';
        $message .= '<p>' . $this->generateFormulationSummary($formulation) . '</p>';
        $message .= '</div>';
        
        $message .= '<div style="text-align: center; margin-top: 30px; padding: 20px; background-color: #f1f2f6; border-radius: 8px;">';
        $message .= '<p><strong>Please review this request and prepare the custom formulation.</strong></p>';
        $message .= '<p><strong>Reply to:</strong> <a href="mailto:' . $customerEmail . '">' . $customerEmail . '</a></p>';
        $message .= '</div>';
        
        return $message;
    }
    
    /**
     * Format ingredient names to be more readable
     */
    private function formatIngredientName($ingredient) {
        $formatted = str_replace('-', ' ', $ingredient);
        $formatted = ucwords($formatted);
        
        // Handle special cases
        $replacements = [
            'Vitamin C' => 'Vitamin C (L-Ascorbic Acid)',
            'Hyaluronic Acid' => 'Hyaluronic Acid (HA)',
            'Salicylic Acid' => 'Salicylic Acid (BHA)',
            'Green Tea' => 'Green Tea Extract',
            'Avena Sativa' => 'Oat Extract (Avena Sativa)',
            'Beta Vulgaris' => 'Beetroot Extract (Beta Vulgaris)',
            'Sodium Pca' => 'Sodium PCA',
            'Copper Peptides' => 'Copper Peptides (GHK-Cu)'
        ];
        
        return $replacements[$formatted] ?? $formatted;
    }
    
    /**
     * Format selections array to readable string
     */
    private function formatSelections($selections) {
        if (empty($selections)) {
            return 'None selected';
        }
        
        return implode(', ', array_map('ucfirst', $selections));
    }
    
    /**
     * Generate a professional formulation summary
     */
    private function generateFormulationSummary($formulation) {
        $skinTypes = $this->formatSelections($formulation['skinType']);
        $format = ucfirst($formulation['baseFormat']);
        
        $summary = "Custom {$format} formulation for {$skinTypes} skin";
        
        $totalIngredients = 0;
        if (!empty($formulation['keyActives'])) {
            $totalIngredients += count($formulation['keyActives']);
        }
        if (!empty($formulation['extracts'])) {
            $totalIngredients += count($formulation['extracts']);
        }
        if (!empty($formulation['boosters'])) {
            $totalIngredients += count($formulation['boosters']);
        }
        
        if ($totalIngredients > 0) {
            $summary .= " featuring {$totalIngredients} selected ingredients";
        }
        
        return $summary;
    }
    
    private function sendEmail($to, $subject, $message) {
        // Always log emails for debugging
        $this->logEmail($to, $subject, $message);
        
        // If SMTP credentials are not configured, just log and return success
        if (empty($this->smtpConfig['username']) || empty($this->smtpConfig['password'])) {
            error_log("SMTP not configured - email logged only. Configure SMTP_USERNAME and SMTP_PASSWORD environment variables.");
            return true; // Return true for development
        }
        
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpConfig['username'];
            $mail->Password = $this->smtpConfig['password'];
            $mail->SMTPSecure = $this->smtpConfig['encryption'];
            $mail->Port = $this->smtpConfig['port'];
            
            // Set encoding
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            
            // Content - Send as HTML with plain text alternative
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $this->convertToHtml($message);
            $mail->AltBody = $message; // Plain text version
            
            $mail->send();
            error_log("Email sent successfully via SMTP to: $to");
            return true;
        } catch (Exception $e) {
            error_log("SMTP Email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convert plain text message to HTML format
     */
    private function convertToHtml($message) {
        // Convert line breaks to HTML
        $html = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        
        // Wrap in proper HTML structure
        $htmlMessage = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skincare Formulation Request</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .email-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .section {
            margin: 25px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            border-radius: 4px;
        }
        .section h3 {
            color: #2c3e50;
            margin-top: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .ingredient-list {
            list-style: none;
            padding-left: 0;
        }
        .ingredient-list li {
            padding: 5px 0;
            border-bottom: 1px dotted #ddd;
        }
        .ingredient-list li:before {
            content: "• ";
            color: #e74c3c;
            font-weight: bold;
        }
        .summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .customer-info {
            background-color: #e8f5e8;
            border-left-color: #27ae60;
        }
        .skin-profile {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }
        .actives {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
        .extracts {
            background-color: #d4edda;
            border-left-color: #28a745;
        }
        .hydrators {
            background-color: #cce7ff;
            border-left-color: #007bff;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🧴 New Skincare Formulation Request 🧴</h1>
        </div>
        
        <div class="content">
            ' . $html . '
        </div>
        
        <div class="footer">
            <p><strong>Skincare Formulation App</strong><br>
            Professional Custom Skincare Solutions</p>
        </div>
    </div>
</body>
</html>';
        
        return $htmlMessage;
    }
    
    private function logEmail($to, $subject, $message) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'subject' => $subject,
            'message' => $message
        ];
        
        error_log("EMAIL LOG: " . json_encode($logEntry));
    }
}
?>