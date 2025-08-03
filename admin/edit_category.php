<?php
session_start();
if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$categories_file_path = __DIR__ . '/../categories.json';
$categories = file_exists($categories_file_path) ? json_decode(file_get_contents($categories_file_path), true) : [];

$category_id = $_GET['id'] ?? null;
$category_data = ['id' => '', 'name' => '', 'icon' => ''];
$is_editing = false;

if ($category_id) {
    $category_index = array_search($category_id, array_column($categories, 'id'));
    if ($category_index !== false) {
        $category_data = $categories[$category_index];
        $is_editing = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $is_editing ? 'Edit' : 'Add'; ?> Category</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8F87F1;
            --text-color: #343f52;
            --background-color: #f5f7fa;
            --card-bg-color: #ffffff;
            --border-color: #e3e8ee;
        }
        body { font-family: sans-serif; background-color: var(--background-color); margin: 0; }
        .content-card { background: var(--card-bg-color); padding: 2rem; margin: 2rem; border-radius: 6px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: .5rem; }
        input { width: 100%; padding: .5rem; }
        .btn-primary { background-color: var(--primary-color); color: white; padding: 0.7rem 1.2rem; border: none; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="admin-page-content">
        <div class="content-card">
            <h1><?php echo $is_editing ? 'Edit' : 'Add'; ?> Category</h1>
            <form action="save_category.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($category_data['id']); ?>">
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category_data['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="icon">Font Awesome Icon Class</label>
                    <input type="text" id="icon" name="icon" value="<?php echo htmlspecialchars($category_data['icon']); ?>" placeholder="e.g., fas fa-book-open" required>
                </div>
                <button type="submit" class="btn-primary"><?php echo $is_editing ? 'Update' : 'Save'; ?> Category</button>
            </form>
        </div>
    </div>
</body>
</html>
