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
foreach ($coupons as $coupon) {
    if ($coupon['code'] === $couponCode) {
        $coupon_found = $coupon;
        break;
    }
}

if ($coupon_found) {
    echo json_encode(['success' => true, 'discount' => $coupon_found['discount']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid coupon code.']);
}
?>
