<?php
/**
 * 📧 GUARANTEED SMTP DEBUGGER
 * Visit: http://localhost:8000/backend/test-mail.php
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/config.php';

// Try standard Composer autoload first
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    die("<h2>❌ Error: vendor/autoload.php not found!</h2>
         <p>Run these commands in terminal:</p>
         <pre>cd d:/drsk/salon-style-clone-main/salon-style-clone-main/backend
C:\\xampp\\php\\php.exe composer.phar install</pre>");
}
require_once $autoload;

echo "<h1>📧 SMTP Deep Debugger</h1>";
echo "<p>Testing Connection for: <b>amanajeetthakur644@gmail.com</b></p>";
echo "<hr><pre style='background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; font-family: monospace; height: 400px; overflow: auto;'>";

$mail = new PHPMailer(true);

try {
    // 1. SMTP SERVER SETTINGS
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;  // SHOW FULL LOG
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // USES VALUES FROM config.php
    $mail->Username = 'amanajeetthakur644@gmail.com';
    $mail->Password = SMTP_PASS; // App Password from config.php

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // 2. RECIPIENTS
    $mail->setFrom('amanajeetthakur644@gmail.com', 'Salon App Debug');
    $mail->addAddress('amanajeetthakur644@gmail.com');

    // 3. CONTENT
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test - ' . date('H:i:s');
    $mail->Body = '<b>Success!</b> SMTP is working 100%.';

    $mail->send();
    echo "</pre><hr>";
    echo "<h2 style='color: #2ecc71;'>✅ SUCCESS! Email was delivered.</h2>";
} catch (Exception $e) {
    echo "</pre><hr>";
    echo "<h2 style='color: #e74c3c;'>❌ FAILED!</h2>";
    echo "<p><b>Error Message:</b> " . $e->getMessage() . "</p>";

    echo "<h3>Check these 3 things immediately:</h3>";
    echo "<ol>
            <li><b>App Password:</b> Are you using a 16-character code? (Gmail login password DOES NOT WORK).</li>
            <li><b>OpenSSL:</b> Check your XAMPP Apache config (php.ini) for <code>extension=openssl</code>.</li>
            <li><b>Port 587:</b> Some office/public Wi-Fi blocks this port. Try a mobile hotspot.</li>
          </ol>";
}
