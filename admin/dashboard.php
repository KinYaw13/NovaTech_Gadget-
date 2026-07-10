<?php
$page_title = 'Admin Dashboard | NovaTech Gadgets';
$active = 'admin';
$base = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/messages.php';
require_once __DIR__ . '/../includes/notifications.php';
require_admin();
ensure_customer_engagement_schema($pdo);

$totalProducts = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
$totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalCustomers = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$revenue = (float) $pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM orders')->fetchColumn();
$recentOrders = $pdo->query('SELECT o.*, u.full_name FROM orders o JOIN users u ON u.user_id = o.user_id ORDER BY o.created_at DESC LIMIT 5')->fetchAll();
$dashboardCounts = admin_notification_counts($pdo);

require_once __DIR__ . '/../includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Admin dashboard</h1><p>Manage the NovaTech PHP + MySQL e-commerce system.</p></div>
    <div class="admin-shell">
      <?php include __DIR__ . '/nav.php'; ?>
      <section class="admin-main">
        <div class="grid">
          <div class="metric-card"><span class="badge">Catalog</span><h2><?= $totalProducts ?></h2><p>Total Products</p></div>
          <div class="metric-card"><span class="badge processing">Orders</span><h2><?= $totalOrders ?></h2><p>Total Orders</p></div>
          <div class="metric-card"><span class="badge delivered">Customers</span><h2><?= $totalCustomers ?></h2><p>Total Customers</p></div>
          <div class="metric-card revenue-card">
            <span class="badge">Revenue</span>
            <h2><span class="currency">RM</span><span class="amount"><?= number_format((float) $revenue, 2) ?></span></h2>
            <p>Total Revenue</p>
          </div>
        </div>
        <div class="grid dashboard-mini-grid">
          <div class="metric-card"><span class="badge processing">Messages</span><h2><?= $dashboardCounts['unread_messages'] ?></h2><p>Unread Messages</p></div>
          <div class="metric-card"><span class="badge pending">Requests</span><h2><?= $dashboardCounts['pending_product_requests'] ?></h2><p>Pending Product Requests</p></div>
          <div class="metric-card"><span class="badge pending">Claims</span><h2><?= $dashboardCounts['pending_warranty'] ?></h2><p>Pending Warranty Claims</p></div>
          <div class="metric-card"><span class="badge processing">Discounts</span><h2><?= $dashboardCounts['active_discounts'] ?></h2><p>Active Discounts</p></div>
          <div class="metric-card"><span class="badge">Vouchers</span><h2><?= $dashboardCounts['vouchers_sent'] ?></h2><p>Vouchers Sent</p></div>
        </div>
        <div class="panel" style="margin-top:24px;">
          <h2>Recent orders</h2>
          <table>
            <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($recentOrders as $order): ?>
                <tr><td>#<?= (int) $order['order_id'] ?></td><td><?= e($order['full_name']) ?></td><td><?= money($order['total_amount']) ?></td><td><span class="badge <?= strtolower($order['order_status']) ?>"><?= e($order['order_status']) ?></span></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
