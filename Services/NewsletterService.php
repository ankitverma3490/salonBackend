<?php
/**
 * Newsletter Service - Handles subscriber notifications
 */

class NewsletterService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Send a welcome email to a new subscriber
     */
    public function sendWelcomeEmail($email)
    {
        try {
            $subject = "Welcome to Noamskin - Your RM 50 Gift is Here!";
            $discountCode = "SUB50";

            $htmlMessage = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <div style='text-align: center; margin-bottom: 30px;'>
                        <h1 style='color: #1a1a1a; margin-bottom: 10px;'>Welcome to Noamskin!</h1>
                        <p style='color: #666; font-size: 16px;'>Thank you for joining our exclusive beauty network.</p>
                    </div>
                    
                    <div style='background-color: #f9f9f9; padding: 30px; border-radius: 8px; text-align: center; margin-bottom: 30px;'>
                        <h2 style='color: #333; margin-top: 0;'>Your Welcome Gift</h2>
                        <p style='font-size: 18px; color: #444; margin-bottom: 20px;'>Enjoy <strong>RM 50 OFF</strong> your first booking with us!</p>
                        
                        <div style='background-color: #000; color: #fff; display: inline-block; padding: 15px 30px; font-size: 24px; font-weight: bold; border-radius: 5px; letter-spacing: 2px; margin-bottom: 15px;'>
                            $discountCode
                        </div>
                        
                        <p style='color: #888; font-size: 12px;'>Use this code at checkout. Valid for first-time visits only.</p>
                    </div>
                    
                    <div style='margin-bottom: 30px;'>
                        <h3 style='color: #333;'>What's next?</h3>
                        <ul style='color: #555; line-height: 1.6;'>
                            <li>Explore our premium <a href='#' style='color: #000; text-decoration: underline;'>salons</a></li>
                            <li>Book your next beauty <a href='#' style='color: #000; text-decoration: underline;'>service</a></li>
                            <li>Stay tuned for exclusive wellness tips in your inbox</li>
                        </ul>
                    </div>
                    
                    <hr style='border: 0; border-top: 1px solid #eee; margin-bottom: 20px;'>
                    
                    <div style='text-align: center; color: #999; font-size: 12px;'>
                        <p>&copy; " . date('Y') . " Noamskin Beauty Network. All rights reserved.</p>
                        <p>You received this email because you subscribed to our newsletter.</p>
                    </div>
                </div>
            ";

            // Attempt to send via EmailService
            if (class_exists('EmailService')) {
                EmailService::send($email, $subject, $htmlMessage);
            }

            // Also log to the local newsletter log for backup/debugging
            $logFile = __DIR__ . '/../../logs/newsletter_emails.log';
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0777, true);
            }

            $logEntry = "[" . date('Y-m-d H:i:s') . "] 📧 WELCOME EMAIL TO: $email\n";
            $logEntry .= "SUBJECT: $subject\n";
            $logEntry .= "STATUS: Processed successfully\n";
            $logEntry .= "--------------------------------------------------\n";

            file_put_contents($logFile, $logEntry, FILE_APPEND);

            return true;
        }
        catch (Exception $e) {
            error_log("[NewsletterService] Error sending welcome email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify all subscribers about a new entity (Salon or Service)
     */
    public function notifySubscribers($type, $name, $details = "")
    {
        try {
            // Get all subscribers
            $stmt = $this->db->query("SELECT email FROM newsletter_subscribers");
            $subscribers = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($subscribers)) {
                return false;
            }

            $subject = ($type === 'salon') ? "New Salon on Noamskin: $name" : "New Service Available: $name";
            $message = ($type === 'salon')
                ? "A new premium salon '$name' has joined our network. Explore their services now!"
                : "A new beauty service '$name' is now available. Book your appointment today!";

            if ($details) {
                $message .= "\n\nDetails: $details";
            }

            // In a real application, you would use mail() or a library like PHPMailer here.
            // For this local simulation, we'll log the "sent" emails to a file.
            $logFile = __DIR__ . '/../../logs/newsletter_emails.log';
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0777, true);
            }

            $logEntry = "[" . date('Y-m-d H:i:s') . "] 📧 TO: " . implode(', ', $subscribers) . "\n";
            $logEntry .= "SUBJECT: $subject\n";
            $logEntry .= "BODY: $message\n";
            $logEntry .= "--------------------------------------------------\n";

            file_put_contents($logFile, $logEntry, FILE_APPEND);

            error_log("[NewsletterService] Notification logged for $type: $name to " . count($subscribers) . " users.");
            return true;
        }
        catch (Exception $e) {
            error_log("[NewsletterService] Error: " . $e->getMessage());
            return false;
        }
    }
}
