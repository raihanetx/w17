<?php
session_start();
if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$coupons_file_path = __DIR__ . '/../coupons.json';
$coupons = file_exists($coupons_file_path) ? json_decode(file_get_contents($coupons_file_path), true) : [];
if (!is_array($coupons)) {
    $coupons = [];
}

$coupon_to_edit = null;
$coupon_code = $_GET['code'] ?? null;

if ($coupon_code) {
    foreach ($coupons as $coupon) {
        if ($coupon['code'] === $coupon_code) {
            $coupon_to_edit = $coupon;
            break;
        }
    }
}

if (!$coupon_to_edit) {
    // Optional: Redirect or show an error if coupon not found
    header("Location: coupons.php?error=not_found");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Coupon - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8F87F1;
            --text-color: #343f52;
            --background-color: #f5f7fa;
            --card-bg-color: #ffffff;
            --border-color: #e3e8ee;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: var(--background-color); margin: 0; color: var(--text-color); }
        .admin-wrapper { display: flex; }
        .admin-sidebar { width: 250px; background: var(--card-bg-color); position: fixed; height: 100%; border-right: 1px solid var(--border-color); }
        .admin-main-content { margin-left: 250px; width: calc(100% - 250px); padding: 2rem; }
        .content-card { background: var(--card-bg-color); padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; align-items: flex-end; }
        .form-group { display: flex; flex-direction: column; }
        label { margin-bottom: .5rem; font-weight: 500; font-size: 0.9em; }
        input[type="text"], input[type="number"], input[type="date"] { padding: .75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1em; }
        .btn { padding: .75rem 1.5rem; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; }
        .btn-primary { background-color: var(--primary-color); color: white; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div style="padding: 1.5rem; text-align:center;"><img src="https://i.postimg.cc/4NtztqPt/IMG-20250603-130207-removebg-preview-1.png" alt="Logo" style="max-height: 45px;"></div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="admin_dashboard.php"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a></li>
                    <li><a href="products.php"><i class="fas fa-box-open"></i> <span>Manage Products</span></a></li>
                    <li><a href="categories.php"><i class="fas fa-sitemap"></i> <span>Manage Categories</span></a></li>
                    <li><a href="coupons.php" class="active"><i class="fas fa-tags"></i> <span>Manage Coupons</span></a></li>
                    <li><a href="reviews.php"><i class="fas fa-star"></i> <span>Manage Reviews</span></a></li>
                    <li><a href="../product_code_generator.html" target="_blank"><i class="fas fa-plus-circle"></i> <span>Add Product Helper</span></a></li>
                    <li><a href="admin_dashboard.php?logout=1"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main-content">
            <div class="content-card">
                <h2>Edit Coupon: <?php echo htmlspecialchars($coupon_to_edit['code']); ?></h2>
                <form action="save_coupon.php" method="POST">
                    <input type="hidden" name="is_edit_mode" value="1">
                    <input type="hidden" name="original_code" value="<?php echo htmlspecialchars($coupon_to_edit['code']); ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="code">Coupon Code</label>
                            <input type="text" id="code" name="code" value="<?php echo htmlspecialchars($coupon_to_edit['code']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="discount">Discount %</label>
                            <input type="number" id="discount" name="discount" value="<?php echo htmlspecialchars($coupon_to_edit['discount']); ?>" required step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="usageLimit">Usage Limit</label>
                            <input type="number" id="usageLimit" name="usageLimit" value="<?php echo htmlspecialchars($coupon_to_edit['usageLimit'] ?? '0'); ?>" placeholder="0 for unlimited">
                        </div>
                        <div class="form-group">
                            <label for="expiryDate">Expiry Date</label>
                            <input type="date" id="expiryDate" name="expiryDate" value="<?php echo htmlspecialchars($coupon_to_edit['expiryDate'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
