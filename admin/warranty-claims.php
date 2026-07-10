<?php
$page_title = 'Warranty Claims | NovaTech Gadgets';
$active = 'admin';
$base = '../';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$statuses = ['Pending', 'Approved', 'Rejected', 'Completed'];
$notice = '';

$pdo->exec("CREATE TABLE IF NOT EXISTS warranty_claims (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  order_id INT NOT NULL,
  order_item_id INT NULL,
  product_name VARCHAR(180) NOT NULL,
  reason VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  image_path VARCHAR(255) NULL,
  status ENUM('Pending','Approved','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  admin_note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customer_claims (customer_id, created_at),
  INDEX idx_order_claims (order_id),
  INDEX idx_claim_status (status)
)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claimId = (int) ($_POST['claim_id'] ?? 0);
    $status = $_POST['status'] ?? 'Pending';
    $adminNote = trim((string) ($_POST['admin_note'] ?? ''));

    if ($claimId > 0 && in_array($status, $statuses, true)) {
        $stmt = $pdo->prepare('UPDATE warranty_claims SET status = ?, admin_note = ? WHERE id = ?');
        $stmt->execute([$status, $adminNote, $claimId]);
        $notice = 'Claim updated.';
    }
}

$claims = $pdo->query("
    SELECT wc.*, u.full_name, u.email, o.invoice_no, o.order_status, oi.quantity, oi.price, oi.selected_options
    FROM warranty_claims wc
    JOIN users u ON u.user_id = wc.customer_id
    JOIN orders o ON o.order_id = wc.order_id
    LEFT JOIN order_items oi ON oi.order_item_id = wc.order_item_id
    ORDER BY wc.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Warranty claims</h1><p>Review customer warranty claims and return requests.</p></div>
    <div class="admin-shell">
      <?php include __DIR__ . '/nav.php'; ?>
      <section class="admin-main">
        <?php if ($notice): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
        <div class="panel warranty-claims-list">
          <?php foreach ($claims as $claim): ?>
            <article class="claim-card">
              <div>
                <span class="badge <?= strtolower($claim['status']) ?>"><?= e($claim['status']) ?></span>
                <h2>#<?= (int) $claim['id'] ?> · <?= e($claim['product_name']) ?></h2>
                <p><strong>Customer:</strong> <?= e($claim['full_name']) ?> · <?= e($claim['email']) ?></p>
                <p><strong>Order:</strong> #<?= (int) $claim['order_id'] ?> <?= $claim['invoice_no'] ? '(' . e($claim['invoice_no']) . ')' : '' ?> · <?= e($claim['order_status']) ?></p>
                <p><strong>Reason:</strong> <?= e($claim['reason']) ?></p>
                <p><?= nl2br(e($claim['description'])) ?></p>
                <?php if (!empty($claim['image_path'])): ?><a class="auth-inline-link" href="../<?= e($claim['image_path']) ?>" target="_blank">View uploaded image</a><?php endif; ?>
                <small>Created <?= e($claim['created_at']) ?> · Updated <?= e($claim['updated_at']) ?></small>
              </div>
              <form method="post" class="claim-admin-form">
                <input type="hidden" name="claim_id" value="<?= (int) $claim['id'] ?>">
                <div class="field"><label>Status</label><select name="status"><?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>" <?= $claim['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div>
                <div class="field"><label>Admin note</label><textarea name="admin_note" rows="3" placeholder="Add note for customer"><?= e((string) $claim['admin_note']) ?></textarea></div>
                <button class="btn" type="submit">Save Claim</button>
              </form>
            </article>
          <?php endforeach; ?>
          <?php if (!$claims): ?>
            <div class="empty-state">
              <h2>No warranty claims yet</h2>
              <p>Customer warranty claims and return requests will appear here for admin review.</p>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
