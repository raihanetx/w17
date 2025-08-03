<?php
session_start();
if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$coupons_file_path = __DIR__ . '/../coupons.json';
$coupons = file_exists($coupons_file_path) ? json_decode(file_get_contents($coupons_file_path), true) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Coupons - Admin Panel</title>
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
        .admin-wrapper { display: flex; }
        .admin-sidebar { width: 250px; background: var(--card-bg-color); position: fixed; height: 100%; border-right: 1px solid var(--border-color); }
        .admin-main-content { margin-left: 250px; width: calc(100% - 250px); }
        .admin-page-content { padding: 2rem; }
        .content-card { background: var(--card-bg-color); padding: 2rem; border-radius: 6px; }
        .form-inline { display: flex; gap: 1rem; margin-bottom: 2rem; }
        input[type="text"], input[type="number"] { padding: .5rem; border: 1px solid var(--border-color); border-radius: 4px; }
        .btn { padding: .5rem 1rem; border-radius: 4px; border: none; cursor: pointer; }
        .btn-primary { background-color: var(--primary-color); color: white; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: left; }
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
            <div class="admin-page-content">
                <div class="content-card">
                    <h2>Add New Coupon</h2>
                    <form action="save_coupon.php" method="POST" class="form-inline">
                        <input type="text" name="code" placeholder="Coupon Code" required>
                        <input type="number" name="discount" placeholder="Discount %" required step="0.01">
                        <button type="submit" class="btn btn-primary">Add Coupon</button>
                    </form>

                    <h2>Existing Coupons</h2>
                    <table>
                        <thead><tr><th>Code</th><th>Discount</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($coupons as $coupon): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                                    <td><?php echo htmlspecialchars($coupon['discount']); ?>%</td>
                                    <td>
                                        <a href="delete_coupon.php?code=<?php echo urlencode($coupon['code']); ?>" onclick="return confirm('Are you sure?')">Delete</a>
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
