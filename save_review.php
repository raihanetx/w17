<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$productId = $input['product_id'] ?? null;
$author = trim($input['author'] ?? '');
$rating = $input['rating'] ?? null;
$text = trim($input['text'] ?? '');

if (!$productId || empty($author) || $rating === null || empty($text)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit();
}

if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating value.']);
    exit();
}

$products_file_path = __DIR__ . '/products.json';
$products = [];

if (file_exists($products_file_path)) {
    $json_data = file_get_contents($products_file_path);
    $products = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Error decoding products.json: ' . json_last_error_msg()]);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'products.json file not found.']);
    exit();
}

$product_found_index = -1;
foreach ($products as $index => $product) {
    if ($product['id'] == $productId) {
        $product_found_index = $index;
        break;
    }
}

if ($product_found_index === -1) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit();
}

$new_review = [
    'id' => time() . rand(100, 999), // Simple unique ID
    'author' => htmlspecialchars($author),
    'date' => date('Y-m-d'),
    'rating' => intval($rating),
    'text' => htmlspecialchars($text),
    'avatar' => null, // Or handle avatar uploads if needed
    'status' => 'pending',
    'featured' => false
];

if (!isset($products[$product_found_index]['reviews']) || !is_array($products[$product_found_index]['reviews'])) {
    $products[$product_found_index]['reviews'] = [];
}

// Add the new review to the beginning of the array
array_unshift($products[$product_found_index]['reviews'], $new_review);

if (file_put_contents($products_file_path, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully and is pending approval.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save the review.']);
}
?>
