<?php
require_once __DIR__ . '/../Database.php';
try {
     = Database::getInstance()->getConnection();
    // Check if platform_admins table exists, if not create it
    ->exec("CREATE TABLE IF NOT EXISTS platform_admins (
        id VARCHAR(36) PRIMARY KEY,
        user_id VARCHAR(36) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(user_id)
    )");
    
    // Get the first user
     = ->query("SELECT id FROM users LIMIT 1");
     = ->fetch(PDO::FETCH_ASSOC);
    
    if (\) {
         = \['id'];
        ->prepare("INSERT IGNORE INTO platform_admins (id, user_id, is_active) VALUES (UUID(), ?, 1)")
           ->execute([\]);
        echo json_encode(['success' => true, 'user_id' => \, 'message' => 'First user promoted to platform admin']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No users found to promote']);
    }
} catch (Exception \) {
    echo json_encode(['error' => \->getMessage()]);
}
