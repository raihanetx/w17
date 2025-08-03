<?php
session_start();
if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['code']) || !isset($_POST['discount'])) {
    header('Location: coupons.php?error=missing_data');
    exit;
}

$coupons_file_path = __DIR__ . '/../coupons.json';
$coupons = file_exists($coupons_file_path) ? json_decode(file_get_contents($coupons_file_path), true) : [];
if (!is_array($coupons)) {
    $coupons = [];
}

$is_edit_mode = isset($_POST['is_edit_mode']) && $_POST['is_edit_mode'] == '1';
$coupon_code = strtoupper(trim($_POST['code']));
$original_code = $is_edit_mode ? strtoupper(trim($_POST['original_code'])) : null;

// Check for duplicate coupon codes, excluding the one being edited
$code_exists = false;
foreach ($coupons as $coupon) {
    if ($coupon['code'] === $coupon_code) {
        if ($is_edit_mode && $coupon['code'] === $original_code) {
            continue; // It's the same coupon, so that's fine
        }
        $code_exists = true;
        break;
    }
}

if ($code_exists) {
    header('Location: coupons.php?error=code_exists');
    exit;
}

$coupon_data = [
    'code' => $coupon_code,
    'discount' => floatval($_POST['discount']),
    'usageLimit' => intval($_POST['usageLimit'] ?? 0),
    'expiryDate' => trim($_POST['expiryDate'] ?? '')
];

if ($is_edit_mode) {
    // Edit existing coupon
    $updated = false;
    foreach ($coupons as $index => $coupon) {
        if ($coupon['code'] === $original_code) {
            // Preserve timesUsed when editing
            $coupon_data['timesUsed'] = $coupon['timesUsed'] ?? 0;
            $coupons[$index] = $coupon_data;
            $updated = true;
            break;
        }
    }
} else {
    // Add new coupon
    $coupon_data['timesUsed'] = 0;
    $coupons[] = $coupon_data;
}

if (file_put_contents($coupons_file_path, json_encode($coupons, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    header('Location: coupons.php?success=1');
} else {
    header('Location: coupons.php?error=write_failed');
}
exit;
?>
