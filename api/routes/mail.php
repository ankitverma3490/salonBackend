<?php
/**
 * 📧 MAIL ROUTES
 */

if ($method === 'POST') {
    $body = getRequestBody();
    $to = $body['to'] ?? '';
    $subject = $body['subject'] ?? 'Test Email from Salon App';
    $message = $body['message'] ?? 'This is a test email sent via PHPMailer and Gmail SMTP.';

    if (empty($to)) {
        sendResponse(['error' => 'Recipient email is required'], 400);
    }

    // Basic validation
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        sendResponse(['error' => 'Invalid email format'], 400);
    }

    // Call EmailService
    $result = EmailService::send($to, $subject, "<h1>Testing SMTP Delivery</h1><p>$message</p>");

    if ($result['success']) {
        sendResponse($result);
    } else {
        sendResponse($result, 500);
    }
} else {
    sendResponse(['error' => 'Method not allowed'], 405);
}
