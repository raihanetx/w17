<?php
session_start();

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$products_file_path = __DIR__ . '/../products.json';
$products = file_exists($products_file_path) ? json_decode(file_get_contents($products_file_path), true) : [];

$product_id = $_POST['id'] ?? null;
$product_data = [
    'name' => $_POST['name'] ?? '',
    'description' => $_POST['description'] ?? '',
    'longDescription' => $_POST['longDescription'] ?? '',
    'category' => $_POST['category'] ?? 'uncategorized',
    'price' => floatval($_POST['price'] ?? 0),
    'image' => $_POST['image'] ?? '',
    'isFeatured' => isset($_POST['isFeatured']),
    'stock' => intval($_POST['stock'] ?? 0),
    'durations' => json_decode($_POST['durations'] ?? '[]', true),
    'reviews' => []
];

if ($product_id) { // Editing existing product
    $product_index = array_search($product_id, array_column($products, 'id'));
    if ($product_index !== false) {
        $product_data['id'] = $product_id;
        // Preserve existing reviews and other data not in the form
        $product_data['reviews'] = $products[$product_index]['reviews'] ?? [];
        $products[$product_index] = $product_data;
    }
} else { // Adding new product
    $new_id = empty($products) ? 1 : max(array_column($products, 'id')) + 1;
    $product_data['id'] = $new_id;
    $products[] = $product_data;
}

file_put_contents($products_file_path, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header('Location: products.php');
exit;
?>
