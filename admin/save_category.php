<?php
session_start();
if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: categories.php');
    exit;
}

$categories_file_path = __DIR__ . '/../categories.json';
$categories = file_exists($categories_file_path) ? json_decode(file_get_contents($categories_file_path), true) : [];

$category_id = $_POST['id'] ?? null;
$category_data = [
    'id' => $category_id ?: strtolower(str_replace(' ', '-', $_POST['name'])),
    'name' => $_POST['name'] ?? '',
    'icon' => $_POST['icon'] ?? ''
];

if ($category_id) { // Editing
    $category_index = array_search($category_id, array_column($categories, 'id'));
    if ($category_index !== false) {
        $categories[$category_index] = $category_data;
    }
} else { // Adding
    $categories[] = $category_data;
}

file_put_contents($categories_file_path, json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header('Location: categories.php');
exit;
?>
