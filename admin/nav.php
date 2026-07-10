<?php
require_once __DIR__ . '/../includes/notifications.php';
$navCounts = admin_notification_counts($pdo);
?>
<aside class="panel admin-nav">
  <div class="admin-menu">
    <a href="dashboard.php">Overview</a>
    <a href="products.php">Products</a>
    <a href="orders.php">Orders</a>
    <a href="product-requests.php">AI Requests<?= nav_badge($navCounts['pending_product_requests']) ?></a>
    <a href="messages.php">Messages<?= nav_badge($navCounts['unread_messages']) ?></a>
    <a href="reviews.php">Reviews</a>
    <a href="vouchers.php">Vouchers</a>
    <a href="discounts.php">Discounts</a>
    <a href="warranty-claims.php">Warranty Claims<?= nav_badge($navCounts['pending_warranty']) ?></a>
    <a href="customers.php">Customers</a>
    <a href="../home.php">Back to Store</a>
  </div>
</aside>
