<?php
$res = file_get_contents('http://localhost:8000/api/coupons/validate/SAVE20');
echo "Result: " . $res;
