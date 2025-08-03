<?php
session_start();
if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$coupon_code = $_GET['code'] ?? null;

if (!$coupon_code) {
    header('Location: coupons.php');
    exit;
}

$coupons_file_path = __DIR__ . '/../coupons.json';
$coupons = file_exists($coupons_file_path) ? json_decode(file_get_contents($coupons_file_path), true) : [];

$coupon_index = array_search($coupon_code, array_column($coupons, 'code'));

if ($coupon_index !== false) {
    array_splice($coupons, $coupon_index, 1);
    file_put_contents($coupons_file_path, json_encode($coupons, JSON_PRETTY_PRINT));
}

header('Location: coupons.php');
exit;
?>
