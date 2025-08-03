<?php
session_start();
if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$category_id = $_GET['id'] ?? null;

if (!$category_id) {
    header('Location: categories.php');
    exit;
}

$categories_file_path = __DIR__ . '/../categories.json';
$categories = file_exists($categories_file_path) ? json_decode(file_get_contents($categories_file_path), true) : [];

$category_index = array_search($category_id, array_column($categories, 'id'));

if ($category_index !== false) {
    array_splice($categories, $category_index, 1);
    file_put_contents($categories_file_path, json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

header('Location: categories.php');
exit;
?>
