<?php
session_start();

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

$products_file_path = __DIR__ . '/../products.json';
$products = file_exists($products_file_path) ? json_decode(file_get_contents($products_file_path), true) : [];

$product_index = array_search($product_id, array_column($products, 'id'));

if ($product_index !== false) {
    array_splice($products, $product_index, 1);
    file_put_contents($products_file_path, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

header('Location: products.php');
exit;
?>
