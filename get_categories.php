<?php
header('Content-Type: application/json');
$categories_json_path = __DIR__ . '/categories.json';

if (file_exists($categories_json_path)) {
    $json_data = file_get_contents($categories_json_path);
    if ($json_data === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not read categories data.']);
        exit;
    }
    if (trim($json_data) === '') {
        echo '[]';
        exit;
    }
    json_decode($json_data);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo $json_data;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Categories data is corrupted. Invalid JSON format.']);
    }
} else {
    echo '[]';
}
?>
