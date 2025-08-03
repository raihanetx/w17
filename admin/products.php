<?php
session_start();

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$products_file_path = __DIR__ . '/../products.json';
$products = [];
$json_load_error = null;

if (file_exists($products_file_path)) {
    $json_data = file_get_contents($products_file_path);
    if ($json_data === false) {
        $json_load_error = "Could not read products.json file.";
    } else {
        $products = json_decode($json_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $json_load_error = "Error decoding products.json: " . json_last_error_msg();
            $products = [];
        }
    }
} else {
    $json_load_error = "products.json file not found.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Panel</title>
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-family-sans-serif);
            background-color: var(--background-color);
            color: var(--text-color);
        }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar {
            background-color: var(--sidebar-bg);
            width: 250px;
            padding: 1.75rem 1.25rem;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1001;
            box-shadow: var(--box-shadow);
            transform: translateX(-100%);
        }
        .admin-sidebar.open { transform: translateX(0); }
        .admin-main-content {
            width: 100%;
            padding: 0;
            transition: margin-left 0.3s ease;
        }
        .admin-topbar {
            background-color: var(--card-bg-color);
            padding: 0.85rem 2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .admin-topbar .sidebar-toggle {
            font-size: 1.4rem;
            cursor: pointer;
            color: var(--text-muted);
            margin-right: 1.5rem;
        }
        .admin-topbar h1 {
            font-size: 1.3rem;
            color: var(--text-color);
            margin: 0;
            font-weight: 600;
        }
        .admin-page-content { padding: 2rem; }
        .content-card {
            background-color: var(--card-bg-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid var(--border-color);
            padding: 1.75rem;
            margin-bottom: 2rem;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        .card-header h2 {
            font-size: 1.15rem;
            color: var(--text-color);
            margin: 0;
        }
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            border: 1px solid var(--primary-color);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: var(--primary-color-darker);
            border-color: var(--primary-color-darker);
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
        }
        .products-table th, .products-table td {
            border-bottom: 1px solid var(--border-color);
            padding: 0.9rem 1rem;
            text-align: left;
        }
        .products-table th {
            background-color: #f8f9fa;
            font-weight: 500;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="logo-admin">
                <img src="https://i.postimg.cc/4NtztqPt/IMG-20250603-130207-removebg-preview-1.png" alt="THINK PLUS BD Logo">
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="admin_dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a></li>
                    <li><a href="products.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>"><i class="fas fa-box-open"></i> <span>Manage Products</span></a></li>
                    <li><a href="categories.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'categories.php') ? 'active' : ''; ?>"><i class="fas fa-sitemap"></i> <span>Manage Categories</span></a></li>
                    <li><a href="coupons.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'coupons.php') ? 'active' : ''; ?>"><i class="fas fa-tags"></i> <span>Manage Coupons</span></a></li>
                    <li><a href="reviews.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'reviews.php') ? 'active' : ''; ?>"><i class="fas fa-star"></i> <span>Manage Reviews</span></a></li>
                    <li><a href="../product_code_generator.html" target="_blank"><i class="fas fa-plus-circle"></i> <span>Add Product Helper</span></a></li>
                    <li><a href="admin_dashboard.php?logout=1"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main-content" id="adminMainContent">
            <header class="admin-topbar">
                <div style="display:flex; align-items:center;">
                    <i class="fas fa-bars sidebar-toggle" id="sidebarToggle"></i>
                    <h1>Manage Products</h1>
                </div>
                <a href="admin_dashboard.php?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            <div class="admin-page-content">
                <div class="content-card">
                    <div class="card-header">
                        <h2>All Products</h2>
                        <a href="edit_product.php" class="btn-primary">Add New Product</a>
                    </div>
                    <div class="products-table-container">
                        <?php if ($json_load_error): ?>
                            <p><?php echo $json_load_error; ?></p>
                        <?php elseif (empty($products)): ?>
                            <p>No products found.</p>
                        <?php else: ?>
                            <table class="products-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['id']); ?></td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                                            <td>৳<?php echo htmlspecialchars($product['price']); ?></td>
                                            <td>
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>">Edit</a>
                                                <a href="delete_product.php?id=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        // Basic sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('adminSidebar').classList.toggle('open');
        });
    </script>
</body>
</html>
