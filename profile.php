<?php
$page_title = 'Profile | NovaTech Gadgets';
$active = 'profile';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/messages.php';
require_customer();
ensure_customer_engagement_schema($pdo);

$userId = (int) $_SESSION['user']['user_id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch();

$orders = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$orders->execute([$userId]);
$orders = $orders->fetchAll();
$requiredFields = [
    'Name' => trim((string) ($profile['full_name'] ?? '')) !== '',
    'Email' => trim((string) ($profile['email'] ?? '')) !== '',
    'Phone number' => trim((string) ($profile['phone'] ?? '')) !== '',
    'Address' => trim((string) ($profile['address'] ?? '')) !== '',
];
$completedCount = count(array_filter($requiredFields));
$completionPercent = (int) round(($completedCount / count($requiredFields)) * 100);
$missingFields = array_keys(array_filter($requiredFields, fn($ok) => !$ok));
$voucherStmt = $pdo->prepare("SELECT * FROM vouchers WHERE customer_id = ? AND status = 'active' AND expires_at >= NOW() ORDER BY created_at DESC");
$voucherStmt->execute([$userId]);
$activeVouchers = $voucherStmt->fetchAll();
$claimsStmt = $pdo->prepare('SELECT * FROM warranty_claims WHERE customer_id = ? ORDER BY created_at DESC LIMIT 8');
$claimsStmt->execute([$userId]);
$claims = $claimsStmt->fetchAll();
$deliveryLabels = [
    'standard' => 'Standard Delivery',
    'express' => 'Express Delivery',
    'pickup' => 'Store Pickup',
];
$trackingLabels = [
    'Paid' => 'Payment received',
    'Processing' => 'Order is being processed',
    'Packed' => 'Order has been packed',
    'Shipped' => 'Order is on the way',
    'Delivered' => 'Order delivered',
    'Cancelled' => 'Order cancelled',
];

require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>User profile</h1><p>View your account details and order history.</p></div>
    <?php if (($_GET['ordered'] ?? '') === 'paid'): ?><div class="notice">Payment successful. Your invoice has been sent to your email.<?php if (!empty($_GET['invoice'])): ?> Invoice: <?= e($_GET['invoice']) ?><?php endif; ?></div><?php elseif (isset($_GET['ordered'])): ?><div class="notice">Order placed successfully.</div><?php endif; ?>
    <?php if (($_GET['updated'] ?? '') === '1'): ?><div class="notice success">Profile updated successfully.</div><?php endif; ?>
    <?php if (($_GET['claim'] ?? '') === 'submitted'): ?><div class="notice success">Warranty claim / return request submitted successfully.</div><?php endif; ?>
    <div class="profile-layout">
      <aside class="panel profile-side">
        <h2><?= e($profile['full_name']) ?></h2>
        <p><?= e($profile['email']) ?></p>
        <p><?= e($profile['phone'] ?: 'No phone saved') ?></p>
        <p><?= !empty($profile['age']) ? e((string) $profile['age']) . ' years old' : 'No age saved' ?></p>
        <p><?= e($profile['gender'] ?: 'No gender saved') ?></p>
        <?php if (!empty($profile['address'])): ?><p><?= nl2br(e($profile['address'])) ?></p><?php endif; ?>
        <span class="badge delivered"><?= e($profile['role']) ?></span>
        <div class="profile-completion">
          <strong><?= $completionPercent === 100 ? 'Profile Complete' : 'Profile Completion: ' . $completionPercent . '%' ?></strong>
          <?php if ($missingFields): ?><small>Missing: <?= e(implode(', ', $missingFields)) ?></small><?php endif; ?>
        </div>
        <a class="btn profile-edit-btn" href="edit_profile.php">Edit Profile</a>
        <a class="btn secondary profile-edit-btn" href="customer_messages.php">Messages</a>
      </aside>
      <section class="panel profile-main">
        <h2>Active vouchers</h2>
        <?php if ($activeVouchers): ?>
          <div class="voucher-strip">
            <?php foreach ($activeVouchers as $voucher): ?>
              <div class="voucher-card"><strong><?= e($voucher['code']) ?></strong><span>RM <?= number_format((float) $voucher['discount_value'], 2) ?> discount</span><small>Expires <?= e($voucher['expires_at']) ?></small></div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state compact">
            <h3>No vouchers yet</h3>
            <p>Available vouchers will appear here when NovaTech admin sends them to your account.</p>
          </div>
        <?php endif; ?>
        <h2>Order history</h2>
        <?php if ($orders): ?>
          <table>
            <thead><tr><th>Order</th><th>Total</th><th>Delivery</th><th>Tracking Status</th><th>Date</th><th>Warranty / Return</th></tr></thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
                <tr>
                  <td>#<?= (int) $order['order_id'] ?></td>
                  <td><?= money($order['total_amount']) ?></td>
                  <td><?= e($deliveryLabels[$order['delivery_method'] ?? ''] ?? ($order['delivery_method'] ?? 'Standard Delivery')) ?><br><small><?= e($order['delivery_estimate'] ?? '') ?></small></td>
                  <td><span class="badge <?= strtolower($order['order_status']) ?>"><?= e($order['order_status']) ?></span><br><small><?= e($trackingLabels[$order['order_status']] ?? 'Order status updated') ?></small></td>
                  <td><?= e($order['created_at']) ?></td>
                  <td><a class="btn secondary table-action-btn" href="warranty_claim.php?order_id=<?= (int) $order['order_id'] ?>">Claim Warranty / Request Return</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-state compact">
            <h3>No orders yet</h3>
            <p>Your order history will appear here after checkout.</p>
            <a class="btn secondary" href="products.php">Browse Products</a>
          </div>
        <?php endif; ?>
        <h2 style="margin-top:24px">Warranty / return requests</h2>
        <?php if ($claims): ?>
          <table>
            <thead><tr><th>Claim</th><th>Product</th><th>Reason</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach ($claims as $claim): ?>
                <tr>
                  <td>#<?= (int) $claim['id'] ?></td>
                  <td><?= e($claim['product_name']) ?></td>
                  <td><?= e($claim['reason']) ?></td>
                  <td><span class="badge <?= strtolower($claim['status']) ?>"><?= e($claim['status']) ?></span><?php if (!empty($claim['admin_note'])): ?><br><small><?= e($claim['admin_note']) ?></small><?php endif; ?></td>
                  <td><?= e($claim['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-state compact">
            <h3>No warranty claims yet</h3>
            <p>Your warranty claims and return requests will appear here after you submit one from an order.</p>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
