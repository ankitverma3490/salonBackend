<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=salon_booking', 'root', '');
    $stmt = $db->query('SHOW TABLES');
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo $row[0] . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
