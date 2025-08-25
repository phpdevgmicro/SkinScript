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
        
        // Email to admin (you)
        $adminEmail = 'admin@yoursite.com'; // Replace with your email
        $adminSubject = "New Skincare Formulation Request from " . $customerName;
        $adminMessage = $this->generateAdminEmail($formulation);
        $adminSent = $this->sendEmail($adminEmail, $adminSubject, $adminMessage);
        
        return $customerSent && $adminSent;
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
        $message = "New skincare formulation request received:\n\n";
        $customerName = $formulation['contact']['name'] ?? $formulation['contact']['fullName'] ?? 'Customer';
        $message .= "Customer: " . htmlspecialchars($customerName) . "\n";
        $message .= "Email: " . htmlspecialchars($formulation['contact']['email']) . "\n\n";
        
        $message .= "Skin Type: " . implode(', ', $formulation['skinType']) . "\n";
        $message .= "Base Format: " . ucfirst($formulation['baseFormat']) . "\n\n";
        
        if (!empty($formulation['keyActives'])) {
            $message .= "Key Actives:\n";
            foreach ($formulation['keyActives'] as $active) {
                $message .= "- " . ucfirst($active) . "\n";
            }
            $message .= "\n";
        }
        
        if (!empty($formulation['extracts'])) {
            $message .= "Extracts:\n";
            foreach ($formulation['extracts'] as $extract) {
                $message .= "- " . ucfirst($extract) . "\n";
            }
            $message .= "\n";
        }
        
        if (!empty($formulation['boosters'])) {
            $message .= "Boosters:\n";
            foreach ($formulation['boosters'] as $booster) {
                $message .= "- " . ucfirst($booster) . "\n";
            }
            $message .= "\n";
        }
        
        if (!empty($formulation['contact']['concerns'])) {
            $message .= "Additional Concerns: " . htmlspecialchars($formulation['contact']['concerns']) . "\n\n";
        }
        
        $message .= "Submitted: " . date('Y-m-d H:i:s') . "\n";
        $message .= "Formulation ID: " . ($formulation['id'] ?? 'N/A');
        
        return $message;
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
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            $mail->send();
            error_log("Email sent successfully via SMTP to: $to");
            return true;
        } catch (Exception $e) {
            error_log("SMTP Email failed: " . $e->getMessage());
            return false;
        }
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