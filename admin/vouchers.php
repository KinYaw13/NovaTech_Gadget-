<?php
$page_title = 'Vouchers | NovaTech Gadgets';
$active = 'admin';
$base = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/promotions.php';
require_admin();
ensure_customer_engagement_schema($pdo);

$adminId = (int) $_SESSION['user']['user_id'];
$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = (int) ($_POST['customer_id'] ?? 0);
    $customer = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'customer'");
    $customer->execute([$customerId]);
    $customer = $customer->fetch();

    if (!$customer) {
        $error = 'Customer not found.';
    } elseif (!is_profile_complete($customer)) {
        $error = 'Customer profile is not complete yet.';
    } else {
        $result = create_welcome_voucher($pdo, $customerId, $adminId);
        $voucher = $result['voucher'];
        if ($result['created']) {
            create_message(
                $pdo,
                $customerId,
                $adminId,
                'admin',
                $customerId,
                'customer',
                'You Received RM15 Voucher',
                'Thank you for completing your NovaTech Gadgets profile. You have received a RM15 voucher. Use code ' . $voucher['code'] . ' at checkout.',
                $adminId
            );
            $notice = 'RM15 voucher sent to ' . $customer['full_name'] . '.';
        } else {
            $notice = 'This customer already has a welcome voucher.';
        }
    }
}

$customers = $pdo->query("SELECT u.*, v.code, v.status AS voucher_status, v.expires_at FROM users u LEFT JOIN vouchers v ON v.customer_id = u.user_id AND v.voucher_type = 'welcome_profile_completion' WHERE u.role = 'customer' ORDER BY u.profile_completed DESC, u.full_name ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Customer vouchers</h1><p>Send one private RM15 welcome voucher to completed customer profiles.</p></div>
    <div class="admin-shell">
      <?php include __DIR__ . '/nav.php'; ?>
      <section class="admin-main">
        <?php if ($notice): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <div class="panel">
          <table>
            <thead><tr><th>Customer</th><th>Profile</th><th>Voucher</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach ($customers as $customer): ?>
                <?php $complete = is_profile_complete($customer); ?>
                <tr>
                  <td><strong><?= e($customer['full_name']) ?></strong><br><small><?= e($customer['email']) ?></small></td>
                  <td><span class="badge <?= $complete ? 'delivered' : 'pending' ?>"><?= $complete ? 'Completed' : 'Pending' ?></span></td>
                  <td><?php if ($customer['code']): ?><strong><?= e($customer['code']) ?></strong><br><small><?= e($customer['voucher_status']) ?> · <?= e($customer['expires_at']) ?></small><?php else: ?>No voucher<?php endif; ?></td>
                  <td>
                    <form method="post">
                      <input type="hidden" name="customer_id" value="<?= (int) $customer['user_id'] ?>">
                      <button class="btn" type="submit" <?= !$complete || $customer['code'] ? 'disabled' : '' ?>>Send RM15 Voucher</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$customers): ?>
                <tr><td class="empty-table-cell" colspan="4">
                  <div class="empty-state compact">
                    <h3>No customers yet</h3>
                    <p>Registered customers will appear here before vouchers can be sent.</p>
                  </div>
                </td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
