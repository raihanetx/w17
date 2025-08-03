<?php
session_start();

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$products_file_path = __DIR__ . '/../products.json';
$products = [];
if (file_exists($products_file_path)) {
    $products = json_decode(file_get_contents($products_file_path), true);
}

$product_id = $_GET['id'] ?? null;
$product_data = [
    'id' => '',
    'name' => '',
    'description' => '',
    'longDescription' => '',
    'category' => '',
    'price' => '',
    'image' => '',
    'isFeatured' => false,
    'stock' => 0,
    'durations' => [],
];
$is_editing = false;

if ($product_id) {
    $product_index = array_search($product_id, array_column($products, 'id'));
    if ($product_index !== false) {
        $product_data = array_merge($product_data, $products[$product_index]);
        $is_editing = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_editing ? 'Edit' : 'Add'; ?> Product - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8F87F1;
            --primary-color-rgb: 143, 135, 241;
            --text-color: #343f52;
            --text-muted: #778398;
            --background-color: #f5f7fa;
            --card-bg-color: #ffffff;
            --border-color: #e3e8ee;
            --sidebar-bg: #ffffff;
            --sidebar-text: #525f7f;
            --sidebar-icon-color: #8898aa;
            --sidebar-hover-bg: #f5f7fa;
            --sidebar-hover-text: var(--primary-color);
            --sidebar-active-bg: rgba(var(--primary-color-rgb), 0.1);
            --sidebar-active-text: var(--primary-color);
            --sidebar-active-icon-color: var(--primary-color);
            --font-family-sans-serif: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            --box-shadow: 0 0 30px 0 rgba(82,63,105,0.05);
            --border-radius: 6px;
        }
        body { font-family: var(--font-family-sans-serif); background-color: var(--background-color); color: var(--text-color); margin: 0; }
        .admin-wrapper { display: flex; }
        .admin-sidebar { width: 250px; background: var(--sidebar-bg); position: fixed; height: 100%; box-shadow: var(--box-shadow); }
        .admin-main-content { margin-left: 250px; width: calc(100% - 250px); }
        .admin-topbar { background: var(--card-bg-color); padding: 1rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .admin-topbar h1 { font-size: 1.5rem; }
        .admin-page-content { padding: 2rem; }
        .content-card { background: var(--card-bg-color); padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 500; margin-bottom: .5rem; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: .8rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
        }
        .form-group textarea { min-height: 120px; }
        .form-group input[type="checkbox"] { width: auto; margin-right: .5rem; }
        .btn-primary { background-color: var(--primary-color); color: white; padding: 0.7rem 1.5rem; border-radius: var(--border-radius); text-decoration: none; font-weight: 500; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="logo-admin" style="text-align: center; padding: 1.5rem 0;">
                <img src="https://i.postimg.cc/4NtztqPt/IMG-20250603-130207-removebg-preview-1.png" alt="Logo" style="max-height: 45px;">
            </div>
            <nav class="admin-nav">
                <ul style="list-style:none; padding:0;">
                    <li><a href="admin_dashboard.php" style="display:block; padding: 1rem 1.5rem; color: var(--sidebar-text); text-decoration:none;"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                    <li><a href="products.php" class="active" style="display:block; padding: 1rem 1.5rem; color: var(--sidebar-active-text); background: var(--sidebar-active-bg); text-decoration:none;"><i class="fas fa-box-open"></i> Manage Products</a></li>
                    <li><a href="../product_code_generator.html" target="_blank" style="display:block; padding: 1rem 1.5rem; color: var(--sidebar-text); text-decoration:none;"><i class="fas fa-plus-circle"></i> Add Product Helper</a></li>
                    <li><a href="admin_dashboard.php?logout=1" style="display:block; padding: 1rem 1.5rem; color: var(--sidebar-text); text-decoration:none;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main-content">
            <header class="admin-topbar">
                <h1><?php echo $is_editing ? 'Edit Product' : 'Add New Product'; ?></h1>
                <a href="admin_dashboard.php?logout=1" class="logout-btn">Logout</a>
            </header>
            <div class="admin-page-content">
                <div class="content-card">
                    <form action="save_product.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($product_data['id']); ?>">

                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product_data['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Short Description</label>
                            <textarea id="description" name="description" required><?php echo htmlspecialchars($product_data['description']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="longDescription">Long Description</label>
                            <textarea id="longDescription" name="longDescription"><?php echo htmlspecialchars($product_data['longDescription']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <option value="course" <?php echo ($product_data['category'] == 'course') ? 'selected' : ''; ?>>Course</option>
                                <option value="subscription" <?php echo ($product_data['category'] == 'subscription') ? 'selected' : ''; ?>>Subscription</option>
                                <option value="software" <?php echo ($product_data['category'] == 'software') ? 'selected' : ''; ?>>Software</option>
                                <option value="ebook" <?php echo ($product_data['category'] == 'ebook') ? 'selected' : ''; ?>>Ebook</option>
                                <option value="resource" <?php echo ($product_data['category'] == 'resource') ? 'selected' : ''; ?>>Resource</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product_data['price']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="image">Image URL</label>
                            <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($product_data['image']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product_data['stock']); ?>">
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="isFeatured" value="1" <?php echo $product_data['isFeatured'] ? 'checked' : ''; ?>>
                                Featured Product
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="durations">Durations (JSON format: [{"label": "1 Month", "price": 100},...])</label>
                            <textarea id="durations" name="durations"><?php echo htmlspecialchars(json_encode($product_data['durations'], JSON_PRETTY_PRINT)); ?></textarea>
                        </div>

                        <button type="submit" class="btn-primary"><?php echo $is_editing ? 'Update' : 'Save'; ?> Product</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
