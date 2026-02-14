<?php
header('Content-Type: application/json');
echo json_encode([
    'getallheaders' => function_exists('getallheaders') ? getallheaders() : 'NOT AVAILABLE',
    '_SERVER' => $_SERVER,
]);
