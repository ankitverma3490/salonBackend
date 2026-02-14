<?php
/**
 * Newsletter Routes
 */

// Ensure the table exists
$db->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($method === 'POST' && (count($uriParts) === 1 || (count($uriParts) === 2 && $uriParts[1] === 'subscribe'))) {
    $data = getRequestBody();
    $email = $data['email'] ?? null;

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(['error' => 'Valid email is required'], 400);
    }

    try {
        $stmt = $db->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->execute([$email]);

        // Send welcome email with discount code
        global $newsletterService;
        if (isset($newsletterService)) {
            $newsletterService->sendWelcomeEmail($email);
        }
        else {
            // Fallback if not initialized in global scope
            require_once __DIR__ . '/../../Services/NewsletterService.php';
            $ns = new NewsletterService($db);
            $ns->sendWelcomeEmail($email);
        }

        sendResponse(['message' => 'Successfully subscribed! Use code SUB50 for 50 RM off your first booking.']);
    }
    catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            sendResponse(['message' => 'You are already subscribed!']);
        }
        sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
    }
}
