<?php
require_once __DIR__ . '/backend/Database.php';
try {
     = Database::getInstance()->getConnection();
     = ->query('SELECT id, email FROM users LIMIT 10');
     = ->fetchAll(PDO::FETCH_ASSOC);
     = ->query('SELECT COUNT(*) as count FROM platform_admins');
     = ->fetch(PDO::FETCH_ASSOC)['count'];
    echo json_encode(['users' => , 'admin_count' => ]);
} catch (Exception \) {
    echo json_encode(['error' => \->getMessage()]);
}
