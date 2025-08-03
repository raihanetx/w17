<?php
session_start();

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    // Not logged in, return a JSON error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$productId = $input['product_id'] ?? null;
$reviewId = $input['review_id'] ?? null;
$action = $input['action'] ?? null;

if (!$productId || !$reviewId || !$action) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit();
}

$products_file_path = __DIR__ . '/../products.json';
$products = [];

if (file_exists($products_file_path)) {
    $json_data = file_get_contents($products_file_path);
    $products = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error decoding products.json: ' . json_last_error_msg()]);
        exit();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'products.json file not found.']);
    exit();
}

$product_found = false;
$review_found = false;

foreach ($products as $p_idx => $product) {
    if ($product['id'] == $productId) {
        $product_found = true;
        if (isset($product['reviews']) && is_array($product['reviews'])) {
            foreach ($product['reviews'] as $r_idx => $review) {
                if ($review['id'] == $reviewId) {
                    $review_found = true;
                    switch ($action) {
                        case 'approve':
                            $products[$p_idx]['reviews'][$r_idx]['status'] = 'approved';
                            break;
                        case 'reject':
                            $products[$p_idx]['reviews'][$r_idx]['status'] = 'rejected';
                            break;
                        case 'delete':
                            array_splice($products[$p_idx]['reviews'], $r_idx, 1);
                            break;
                        case 'toggle_feature':
                            $products[$p_idx]['reviews'][$r_idx]['featured'] = !($review['featured'] ?? false);
                            break;
                        default:
                            // Invalid action
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
                            exit();
                    }
                    break 2; // Exit both loops
                }
            }
        }
    }
}

if (!$product_found || !$review_found) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Product or Review not found.']);
    exit();
}

// Save the updated products array back to the file
if (file_put_contents($products_file_path, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Review updated successfully.']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to write to products.json.']);
}

?>
