<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$email = 'ankit@gmail.com';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);

    if ($stmt->rowCount() > 0) {
        echo "Password updated successfully for $email to $password\n";
    }
    else {
        echo "No changes made or user not found.\n";
    }
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
