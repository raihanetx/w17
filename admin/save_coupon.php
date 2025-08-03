<?php
session_start();
if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['code']) || !isset($_POST['discount'])) {
    header('Location: coupons.php');
    exit;
}

$coupons_file_path = __DIR__ . '/../coupons.json';
$coupons = file_exists($coupons_file_path) ? json_decode(file_get_contents($coupons_file_path), true) : [];

$new_coupon = [
    'code' => strtoupper(trim($_POST['code'])),
    'discount' => floatval($_POST['discount'])
];

// Prevent duplicate coupon codes
$code_exists = false;
foreach ($coupons as $coupon) {
    if ($coupon['code'] === $new_coupon['code']) {
        $code_exists = true;
        break;
    }
}

if (!$code_exists) {
    $coupons[] = $new_coupon;
    file_put_contents($coupons_file_path, json_encode($coupons, JSON_PRETTY_PRINT));
}

header('Location: coupons.php');
exit;
?>
