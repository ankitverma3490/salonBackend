<?php
/**
 * ⏰ Reminder Processor Cron Job
 * ----------------------------
 * This script should be run periodically (e.g., every minute or hour)
 * to process pending reminders and send notifications.
 */

// Basic CLI setup
if (php_sapi_name() !== 'cli' && !isset($_GET['secret_trigger'])) {
    die("Access Denied");
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Services/NotificationService.php';

try {
    $db = Database::getInstance()->getConnection();
    $notifService = new NotificationService($db);

    echo "🔍 Checking for pending reminders...\n";

    // Select reminders due now or in the past that haven't been sent
    $stmt = $db->prepare("
        SELECT * FROM reminders 
        WHERE status = 'pending' 
        AND scheduled_at <= NOW()
    ");
    $stmt->execute();
    $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($reminders) . " reminders to process.\n";

    foreach ($reminders as $reminder) {
        echo "Processing reminder {$reminder['id']} for User {$reminder['user_id']}...\n";

        // 1. Send Notification
        // We use the NotificationService to handle both in-app and email
        $success = $notifService->notifyUser(
            $reminder['user_id'],
            $reminder['title'],
            $reminder['message'],
            'reminder', // Type
            '/client-hub', // Link (redirect to client hub to book)
            true // Send Email
        );

        if ($success) {
            // 2. Mark as Sent
            $update = $db->prepare("UPDATE reminders SET status = 'sent', updated_at = NOW() WHERE id = ?");
            $update->execute([$reminder['id']]);
            echo "✅ Sent successfully.\n";
        } else {
            echo "❌ Failed to send.\n";
        }
    }

    echo "Done.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
