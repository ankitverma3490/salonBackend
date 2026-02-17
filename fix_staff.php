<?php
$path = 'api/routes/staff.php';
$content = file_get_contents($path);
$old = '        $query .= " AND s.id IN (SELECT staff_id FROM staff_services WHERE service_id = ?)";';
$new = '        $query .= " AND (s.id IN (SELECT staff_id FROM staff_services WHERE service_id = ?) OR s.id NOT IN (SELECT staff_id FROM staff_services))";';

if (strpos($content, $old) !== false) {
    $newContent = str_replace($old, $new, $content);
    if (file_put_contents($path, $newContent)) {
        echo 'Successfully replaced line.';
    }
    else {
        echo 'Failed to write to file.';
    }
}
else {
    echo 'Old line not found. Current content snippet: ' . substr($content, strpos($content, 'if ($serviceId)'), 200);
}
