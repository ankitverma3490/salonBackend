<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=salon_booking', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "TABLE: profiles\n";
    $stmt = $db->query('DESCRIBE profiles');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
