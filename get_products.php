<?php
header('Content-Type: application/json');
$products_json_path = __DIR__ . '/products.json';

if (file_exists($products_json_path)) {
    $json_data = file_get_contents($products_json_path);
    // Check if file is empty or there was an error reading it
    if ($json_data === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not read products data.']);
        exit;
    }
    // Check if the file is empty, which is valid JSON for an empty array
    if (trim($json_data) === '') {
        echo '[]';
        exit;
    }
    // Check if json is valid before outputting
    json_decode($json_data);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo $json_data;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Products data is corrupted. Invalid JSON format.']);
    }
} else {
    // If the file doesn't exist, return an empty JSON array
    echo '[]';
}
?>
