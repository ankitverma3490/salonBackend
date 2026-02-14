<?php
error_log("[Coupons API] HIT coupons.php");
sendResponse([
    'code' => 'TEST',
    'discount_type' => 'percentage',
    'discount_value' => 10,
    'is_active' => true
]);
