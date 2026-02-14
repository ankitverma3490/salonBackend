<?php
/**
 * 📧 EMAIL SERVICE - PHPMailer Integration
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Check multiple locations for PHPMailer (Composer or Manual)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
else if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

class EmailService
{

    /**
     * Send an email using SMTP
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email content (HTML supported)
     * @param string $plainTextBody Optional plain text fallback
     * @param bool $debug Enable verbose SMTP debugging
     * @return array Success status and message
     */
    public static function send($to, $subject, $body, $plainTextBody = '', $debug = false)
    {
        // Safety check: ensure PHPMailer exists
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return [
                'success' => false,
                'error' => 'PHPMailer library not found. Please run "composer install" in the backend directory.'
            ];
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            if ($debug) {
                $mail->SMTPDebug = 2; // Enable verbose debug output (SMTP::DEBUG_SERVER)
                // Use a simple error log for debug output
                $mail->Debugoutput = 'error_log';
            }
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
            $mail->Port = SMTP_PORT;

            // ⏲️ FASTER FAIL: Set aggressive timeouts (in seconds)
            $mail->Timeout = 5; // Connection timeout
            $mail->Timelimit = 10; // Total time limit for SMTP commands

            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $plainTextBody ?: strip_tags($body);

            $mail->send();
            return ['success' => true, 'message' => 'Email sent successfully'];
        }
        catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
            return ['success' => false, 'error' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"];
        }
    }
}
