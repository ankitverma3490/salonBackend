<?php
require 'backend/Database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query('SELECT COUNT(*) FROM platform_admins');
echo 'Admins: ' . $stmt->fetchColumn();
