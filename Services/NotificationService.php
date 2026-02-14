<?php
/**
 * 📢 Notification Service - Handles In-app and Email Dispatch
 */
class NotificationService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Send a notification to a specific user
     */
    public function notifyUser($userId, $title, $message, $type = 'info', $link = null, $sendEmail = true)
    {
        try {
            // 1. In-app Notification
            $notifId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

            $stmt = $this->db->prepare("
                INSERT INTO notifications (id, user_id, type, title, message, link, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
            ");
            $stmt->execute([$notifId, $userId, $type, $title, $message, $link]);

            // 2. Email Notification (if requested)
            if ($sendEmail) {
                try {
                    $this->sendEmailToUser($userId, $title, $message);
                }
                catch (Exception $e) {
                    error_log("Non-blocking Email Error: " . $e->getMessage());
                }
            }

            return $notifId;
        }
        catch (Exception $e) {
            error_log("Notification Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a notification to all Super Admins
     */
    public function notifyAdmins($title, $message, $type = 'alert', $link = null)
    {
        try {
            // Find all active super admins
            $stmt = $this->db->query("SELECT user_id FROM platform_admins WHERE is_active = 1");
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($admins as $admin) {
                $this->notifyUser($admin['user_id'], $title, $message, $type, $link, false);
            }
            return true;
        }
        catch (Exception $e) {
            error_log("Admin Notification Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Private helper to handle email dispatch
     */
    private function sendEmailToUser($userId, $subject, $message)
    {
        // Get user email
        $stmt = $this->db->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user && !empty($user['email'])) {
            $to = $user['email'];

            $htmlMessage = "
            <html>
            <head>
                <title>{$subject}</title>
                <style>
                    body { font-family: sans-serif; line-height: 1.6; color: #333; }
                    .container { padding: 20px; border: 1px solid #eee; border-radius: 5px; }
                    .header { background: #1a1a1a; color: #fff; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
                    .footer { font-size: 12px; color: #777; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h1>Salon Network</h1></div>
                    <h3>{$subject}</h3>
                    <p>{$message}</p>
                    <div class='footer'>
                        This is an automated message. Please do not reply.
                    </div>
                </div>
            </body>
            </html>
            ";

            // In local dev, mail() might not be configured, so we use EmailService if available
            if (class_exists('EmailService')) {
                error_log("📧 DISPATCHING EMAIL VIA EmailService TO: $to");
                $result = EmailService::send($to, $subject, $htmlMessage);
                if ($result['success']) {
                    return true;
                }
                error_log("EmailService failed: " . ($result['error'] ?? 'Unknown'));
            }

            // [SECURITY/PERFORMANCE] Removed @mail() fallback to prevent infinite hangs on systems without working local sendmail.
            // PHPMailer (EmailService) is now the primary and only dispatch method.
            return false;
        }
        return false;
    }

    /**
     * Specialized method to send high-fidelity HTML invoices
     */
    public function sendInvoiceEmail($userId, $subject, $htmlContent)
    {
        $stmt = $this->db->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user && !empty($user['email'])) {
            $to = $user['email'];

            error_log("📧 INVOICE EMAIL DISPATCH REQUESTED FOR: $to | SUBJECT: $subject");
            if (class_exists('EmailService')) {
                $result = EmailService::send($to, $subject, $htmlContent);
                return $result['success'];
            }
        }
        return false;
    }
}
