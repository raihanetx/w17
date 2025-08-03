<?php
header('Content-Type: application/json');

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!isset($input['couponCode'])) {
    echo json_encode(['success' => false, 'message' => 'Coupon code not provided.']);
    exit;
}

$couponCode = strtoupper(trim($input['couponCode']));

$coupons_file_path = __DIR__ . '/coupons.json';
$coupons = file_exists($coupons_file_path) ? json_decode(file_get_contents($coupons_file_path), true) : [];

$coupon_found = null;
if (is_array($coupons)) {
    foreach ($coupons as $coupon) {
        if ($coupon['code'] === $couponCode) {
            $coupon_found = $coupon;
            break;
        }
    }
}

if ($coupon_found) {
    // Check expiry date
    if (!empty($coupon_found['expiryDate'])) {
        $expiry_timestamp = strtotime($coupon_found['expiryDate']);
        $today_timestamp = strtotime(date('Y-m-d'));
        if ($today_timestamp > $expiry_timestamp) {
            echo json_encode(['success' => false, 'message' => 'This coupon has expired.']);
            exit;
        }
    }

    // Check usage limit
    $usageLimit = $coupon_found['usageLimit'] ?? 0;
    $timesUsed = $coupon_found['timesUsed'] ?? 0;
    if ($usageLimit > 0 && $timesUsed >= $usageLimit) {
        echo json_encode(['success' => false, 'message' => 'This coupon has reached its usage limit.']);
        exit;
    }

    // If all checks pass
    echo json_encode(['success' => true, 'discount' => $coupon_found['discount']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid coupon code.']);
}
?>
