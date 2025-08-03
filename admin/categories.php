<?php
session_start();

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$categories_file_path = __DIR__ . '/../categories.json';
$categories = [];
if (file_exists($categories_file_path)) {
    $categories = json_decode(file_get_contents($categories_file_path), true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8F87F1;
            --primary-color-rgb: 143, 135, 241;
            --text-color: #343f52;
            --background-color: #f5f7fa;
            --card-bg-color: #ffffff;
            --border-color: #e3e8ee;
            --sidebar-bg: #ffffff;
            --sidebar-text: #525f7f;
        }
        body { font-family: sans-serif; background-color: var(--background-color); color: var(--text-color); margin: 0; }
        .admin-wrapper { display: flex; }
        .admin-sidebar { width: 250px; background: var(--sidebar-bg); position: fixed; height: 100%; }
        .admin-main-content { margin-left: 250px; width: calc(100% - 250px); }
        .admin-topbar { background: var(--card-bg-color); padding: 1rem 2rem; border-bottom: 1px solid var(--border-color); }
        .admin-page-content { padding: 2rem; }
        .content-card { background: var(--card-bg-color); padding: 2rem; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: left; }
        .btn-primary { background-color: var(--primary-color); color: white; padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div style="padding: 1.5rem; text-align:center;"><img src="https://i.postimg.cc/4NtztqPt/IMG-20250603-130207-removebg-preview-1.png" alt="Logo" style="max-height: 45px;"></div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="admin_dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a></li>
                    <li><a href="products.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>"><i class="fas fa-box-open"></i> <span>Manage Products</span></a></li>
                    <li><a href="categories.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'categories.php') ? 'active' : ''; ?>"><i class="fas fa-sitemap"></i> <span>Manage Categories</span></a></li>
                    <li><a href="coupons.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'coupons.php') ? 'active' : ''; ?>"><i class="fas fa-tags"></i> <span>Manage Coupons</span></a></li>
                    <li><a href="../product_code_generator.html" target="_blank"><i class="fas fa-plus-circle"></i> <span>Add Product Helper</span></a></li>
                    <li><a href="admin_dashboard.php?logout=1"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main-content">
            <header class="admin-topbar"><h1>Manage Categories</h1></header>
            <div class="admin-page-content">
                <div class="content-card">
                    <a href="edit_category.php" class="btn-primary" style="margin-bottom: 1rem; display: inline-block;">Add New Category</a>
                    <table>
                        <thead><tr><th>ID</th><th>Name</th><th>Icon</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['id']); ?></td>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><i class="<?php echo htmlspecialchars($category['icon']); ?>"></i></td>
                                    <td>
                                        <a href="edit_category.php?id=<?php echo $category['id']; ?>">Edit</a>
                                        <a href="delete_category.php?id=<?php echo $category['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
