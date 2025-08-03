<?php
header('Content-Type: application/json');
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$inputJSON = file_get_contents('php://input');
$orderDataFromClient = json_decode($inputJSON, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($orderDataFromClient) || !isset($orderDataFromClient['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or incomplete order data.']);
    exit;
}

// --- Coupon Usage Update Logic ---
if (isset($orderDataFromClient['coupon']['code']) && !empty($orderDataFromClient['coupon']['code'])) {
    $couponCode = $orderDataFromClient['coupon']['code'];
    $coupons_file_path = __DIR__ . '/coupons.json';

    if (file_exists($coupons_file_path)) {
        $coupons = json_decode(file_get_contents($coupons_file_path), true);
        $coupon_updated = false;

        if (is_array($coupons)) {
            foreach ($coupons as $index => $coupon) {
                if ($coupon['code'] === $couponCode) {
                    $coupons[$index]['timesUsed'] = ($coupon['timesUsed'] ?? 0) + 1;
                    $coupon_updated = true;
                    break;
                }
            }
            if ($coupon_updated) {
                file_put_contents($coupons_file_path, json_encode($coupons, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }
}
// --- End of Coupon Logic ---

$ordersFilePath = __DIR__ . '/orders.json';
$allOrdersCurrentlyInFile = [];

if (file_exists($ordersFilePath)) {
    $existingJsonData = file_get_contents($ordersFilePath);
    if ($existingJsonData !== false && !empty($existingJsonData)) {
        $decodedData = json_decode($existingJsonData, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedData)) {
            $allOrdersCurrentlyInFile = $decodedData;
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error: Corrupted order data file.']);
            exit;
        }
    }
}

$orderDataFromClient['status'] = $orderDataFromClient['status'] ?? 'Pending';
$orderDataFromClient['timestamp'] = $orderDataFromClient['timestamp'] ?? date('c');

$allOrdersCurrentlyInFile[] = $orderDataFromClient;

if (file_put_contents($ordersFilePath, json_encode($allOrdersCurrentlyInFile, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Order saved successfully.', 'orderId' => $orderDataFromClient['id']]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: Could not save the order.']);
}
?>