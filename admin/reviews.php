<?php
session_start();

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$products_file_path = __DIR__ . '/../products.json';
$all_reviews = [];
$json_load_error = null;

if (file_exists($products_file_path)) {
    $json_data = file_get_contents($products_file_path);
    if ($json_data === false) {
        $json_load_error = "Could not read products.json file.";
    } else {
        $products = json_decode($json_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $json_load_error = "Error decoding products.json: " . json_last_error_msg();
        } else {
            // Sort products by ID or name if needed, here we just iterate
            foreach ($products as $product) {
                if (isset($product['reviews']) && is_array($product['reviews'])) {
                    // Sort reviews by date, newest first
                    usort($product['reviews'], function($a, $b) {
                        return strtotime($b['date']) - strtotime($a['date']);
                    });

                    foreach ($product['reviews'] as $review) {
                        $review['product_id'] = $product['id'];
                        $review['product_name'] = $product['name'];
                        $all_reviews[] = $review;
                    }
                }
            }
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
    <title>Manage Reviews - Admin Panel</title>
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
            font-size: 14px;
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
        }
        .admin-main-content { margin-left: 250px; width: calc(100% - 250px); }
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
        .admin-topbar h1 { font-size: 1.3rem; color: var(--text-color); margin: 0; font-weight: 600; }
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
        .card-header h2 { font-size: 1.15rem; color: var(--text-color); margin: 0; }
        .reviews-table { width: 100%; border-collapse: collapse; }
        .reviews-table th, .reviews-table td {
            border-bottom: 1px solid var(--border-color);
            padding: 0.9rem 1rem;
            text-align: left;
            vertical-align: top;
        }
        .reviews-table th { background-color: #f8f9fa; font-weight: 500; color: var(--text-muted); }
        .review-text-cell { max-width: 350px; white-space: pre-wrap; word-wrap: break-word; font-size: 0.9em; }
        .status-badge {
            padding: 4px 10px;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
            border: 1px solid transparent;
        }
        .status-pending { background-color: #fffbe6; color: #b47d00; border-color: #fff1b8; }
        .status-approved { background-color: #f6ffed; color: #389e0d; border-color: #d9f7be; }
        .status-rejected { background-color: #fff1f0; color: #cf1322; border-color: #ffa39e; }
        .action-buttons { display: flex; flex-direction: column; gap: 6px; align-items: flex-start; }
        .action-btn {
            background-color: #fff;
            border: 1px solid #d9d9d9;
            padding: 4px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }
        .action-btn:hover { border-color: var(--primary-color); color: var(--primary-color); }
        .btn-approve { color: #28a745; border-color: #28a745; }
        .btn-approve:hover { background-color: #28a745; color: #fff; }
        .btn-reject { color: #dc3545; border-color: #dc3545; }
        .btn-reject:hover { background-color: #dc3545; color: #fff; }
        .btn-delete { color: #555; }
        .btn-delete:hover { background-color: #555; color: #fff; }
        .btn-feature { color: #ffc107; border-color: #ffc107; }
        .btn-feature.featured { background-color: #ffc107; color: #fff; }
        .btn-feature:hover { background-color: #ffc107; color: #fff; }

        @media (max-width: 768px) {
            .admin-main-content { margin-left: 0; }
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.open { transform: translateX(0); }
            .admin-topbar .sidebar-toggle { display: block; }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="logo-admin" style="text-align: center; margin-bottom: 2rem;">
                <img src="https://i.postimg.cc/4NtztqPt/IMG-20250603-130207-removebg-preview-1.png" alt="THINK PLUS BD Logo" style="max-height: 45px;">
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="admin_dashboard.php"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a></li>
                    <li><a href="products.php"><i class="fas fa-box-open"></i> <span>Manage Products</span></a></li>
                    <li><a href="categories.php"><i class="fas fa-sitemap"></i> <span>Manage Categories</span></a></li>
                    <li><a href="coupons.php"><i class="fas fa-tags"></i> <span>Manage Coupons</span></a></li>
                    <li><a href="reviews.php" class="active"><i class="fas fa-star"></i> <span>Manage Reviews</span></a></li>
                    <li><a href="../product_code_generator.html" target="_blank"><i class="fas fa-plus-circle"></i> <span>Add Product Helper</span></a></li>
                    <li><a href="admin_dashboard.php?logout=1"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main-content" id="adminMainContent">
            <header class="admin-topbar">
                <h1>Manage Reviews</h1>
                <a href="admin_dashboard.php?logout=1" class="logout-btn" style="color:var(--primary-color); text-decoration:none;border:1px solid;padding:5px 10px;border-radius:5px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            <div class="admin-page-content">
                <div class="content-card">
                    <div class="card-header">
                        <h2>All Reviews</h2>
                    </div>
                    <div class="reviews-table-container" style="overflow-x:auto;">
                        <?php if ($json_load_error): ?>
                            <p><?php echo htmlspecialchars($json_load_error); ?></p>
                        <?php elseif (empty($all_reviews)): ?>
                            <p>No reviews found.</p>
                        <?php else: ?>
                            <table class="reviews-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Author</th>
                                        <th>Rating</th>
                                        <th>Review</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_reviews as $review): ?>
                                        <tr data-review-id="<?php echo htmlspecialchars($review['id']); ?>" data-product-id="<?php echo htmlspecialchars($review['product_id']); ?>">
                                            <td><?php echo htmlspecialchars($review['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($review['author']); ?></td>
                                            <td><?php echo htmlspecialchars($review['rating']); ?>/5</td>
                                            <td class="review-text-cell"><?php echo htmlspecialchars($review['text']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower(htmlspecialchars($review['status'] ?? 'pending')); ?>">
                                                    <?php echo htmlspecialchars($review['status'] ?? 'pending'); ?>
                                                </span>
                                            </td>
                                            <td class="action-buttons">
                                                <?php if (($review['status'] ?? 'pending') !== 'approved'): ?>
                                                    <button class="action-btn btn-approve" onclick="handleReviewAction(this, 'approve')">Approve</button>
                                                <?php endif; ?>
                                                <?php if (($review['status'] ?? 'pending') !== 'rejected'): ?>
                                                    <button class="action-btn btn-reject" onclick="handleReviewAction(this, 'reject')">Reject</button>
                                                <?php endif; ?>
                                                <button class="action-btn btn-feature <?php echo ($review['featured'] ?? false) ? 'featured' : ''; ?>" onclick="handleReviewAction(this, 'toggle_feature')">
                                                    <?php echo ($review['featured'] ?? false) ? 'Unfeature' : 'Feature'; ?>
                                                </button>
                                                <button class="action-btn btn-delete" onclick="handleReviewAction(this, 'delete')">Delete</button>
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
    function handleReviewAction(button, action) {
        const row = button.closest('tr');
        const reviewId = row.dataset.reviewId;
        const productId = row.dataset.productId;

        if (!reviewId || !productId) {
            alert('Error: Could not find review or product ID.');
            return;
        }

        if (action === 'delete' && !confirm('Are you sure you want to delete this review permanently?')) {
            return;
        }

        const payload = {
            review_id: reviewId,
            product_id: productId,
            action: action
        };

        button.disabled = true;
        button.textContent = '...';

        fetch('update_review.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // alert('Success: ' + data.message);
                updateUIAfterAction(row, action);
            } else {
                alert('Error: ' + data.message);
                // Reset button text on failure
                button.disabled = false;
                resetButtonText(button, action);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('An unexpected error occurred. Please check the console.');
            button.disabled = false;
            resetButtonText(button, action);
        });
    }

    function updateUIAfterAction(row, action) {
        if (action === 'delete') {
            row.style.transition = 'opacity 0.5s ease';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 500);
            return;
        }

        const statusBadge = row.querySelector('.status-badge');
        const actionCell = row.querySelector('.action-buttons');

        // Re-enable all buttons in the cell before rebuilding
        actionCell.querySelectorAll('.action-btn').forEach(btn => {
            btn.disabled = false;
        });

        let newStatus = '';
        if (action === 'approve') newStatus = 'approved';
        if (action === 'reject') newStatus = 'rejected';

        if (newStatus) {
            statusBadge.textContent = newStatus;
            statusBadge.className = 'status-badge status-' + newStatus;
        }

        const isFeatured = row.querySelector('.btn-feature').classList.contains('featured');
        let newFeaturedState = isFeatured;
        if(action === 'toggle_feature') {
            newFeaturedState = !isFeatured;
        }

        // Rebuild action buttons based on the new state
        rebuildActionButtons(actionCell, newStatus || statusBadge.textContent.trim(), newFeaturedState);
    }

    function rebuildActionButtons(cell, status, isFeatured) {
        let buttonsHTML = '';
        if (status !== 'approved') {
            buttonsHTML += `<button class="action-btn btn-approve" onclick="handleReviewAction(this, 'approve')">Approve</button>`;
        }
        if (status !== 'rejected') {
            buttonsHTML += `<button class="action-btn btn-reject" onclick="handleReviewAction(this, 'reject')">Reject</button>`;
        }
        buttonsHTML += `<button class="action-btn btn-feature ${isFeatured ? 'featured' : ''}" onclick="handleReviewAction(this, 'toggle_feature')">${isFeatured ? 'Unfeature' : 'Feature'}</button>`;
        buttonsHTML += `<button class="action-btn btn-delete" onclick="handleReviewAction(this, 'delete')">Delete</button>`;
        cell.innerHTML = buttonsHTML;
    }

    function resetButtonText(button, action) {
        switch(action) {
            case 'approve': button.textContent = 'Approve'; break;
            case 'reject': button.textContent = 'Reject'; break;
            case 'delete': button.textContent = 'Delete'; break;
            case 'toggle_feature':
                // This is tricky without knowing the previous state, but we can try
                const isFeatured = button.classList.contains('featured');
                button.textContent = isFeatured ? 'Unfeature' : 'Feature';
                break;
        }
    }
    </script>
</body>
</html>
