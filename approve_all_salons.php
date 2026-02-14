<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Update all salons with NULL approval_status to 'approved'
    $stmt = $db->prepare("
        UPDATE salons 
        SET approval_status = 'approved' 
        WHERE approval_status IS NULL OR approval_status = ''
    ");
    $stmt->execute();
    $affected = $stmt->rowCount();

    echo json_encode([
        'success' => true,
        'message' => "Updated $affected salon(s) to approved status",
        'affected_rows' => $affected
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
