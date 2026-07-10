<?php
$page_title = 'Orders | NovaTech Gadgets';
$active = 'admin';
$base = '../';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$orderStatuses = ['Paid', 'Processing', 'Packed', 'Shipped', 'Delivered', 'Cancelled'];
$deliveryLabels = [
    'standard' => 'Standard Delivery',
    'express' => 'Express Delivery',
    'pickup' => 'Store Pickup',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = $_POST['order_status'] ?? 'Paid';
    if (in_array($status, $orderStatuses, true)) {
        $stmt = $pdo->prepare('UPDATE orders SET order_status = ? WHERE order_id = ?');
        $stmt->execute([$status, $orderId]);
    }
    redirect('orders.php');
}

$orders = $pdo->query('SELECT o.*, u.full_name FROM orders o JOIN users u ON u.user_id = o.user_id ORDER BY o.created_at DESC')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Order management</h1><p>View orders and update order status.</p></div>
    <div class="admin-shell">
      <?php include __DIR__ . '/nav.php'; ?>
      <section class="panel admin-main">
        <table>
          <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Delivery</th><th>Status</th><th>Update</th></tr></thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td>#<?= (int) $order['order_id'] ?></td>
                <td><?= e($order['full_name']) ?></td>
                <td><?= money($order['total_amount']) ?></td>
                <td><?= e($deliveryLabels[$order['delivery_method'] ?? ''] ?? ($order['delivery_method'] ?? 'Standard Delivery')) ?><br><small><?= money((float) ($order['delivery_fee'] ?? 0)) ?></small></td>
                <td><span class="badge <?= strtolower($order['order_status']) ?>"><?= e($order['order_status']) ?></span></td>
                <td>
                  <form class="inline-form order-status-form" method="post">
                    <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                    <select name="order_status" aria-label="Order status">
                      <?php foreach ($orderStatuses as $statusOption): ?>
                        <option value="<?= e($statusOption) ?>" <?= $order['order_status'] === $statusOption ? 'selected' : '' ?>><?= e($statusOption) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn secondary" type="submit">Update Status</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
