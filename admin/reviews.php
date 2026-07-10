<?php
$page_title = 'Product Reviews | NovaTech Gadgets';
$active = 'admin';
$base = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/reviews.php';
require_admin();
ensure_product_reviews_schema($pdo);

$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewId = (int) ($_POST['review_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($reviewId > 0 && in_array($action, ['hide', 'unhide', 'delete'], true)) {
        if ($action === 'delete') {
            $stmt = $pdo->prepare('DELETE FROM product_reviews WHERE id = ?');
            $stmt->execute([$reviewId]);
            $notice = 'Review deleted.';
        } else {
            $status = $action === 'hide' ? 'hidden' : 'visible';
            $stmt = $pdo->prepare('UPDATE product_reviews SET status = ? WHERE id = ?');
            $stmt->execute([$status, $reviewId]);
            $notice = 'Review updated.';
        }
    }
}

$reviews = $pdo->query("
    SELECT pr.*, p.product_name, p.brand, u.full_name, u.email
    FROM product_reviews pr
    JOIN products p ON p.product_id = pr.product_id
    JOIN users u ON u.user_id = pr.customer_id
    ORDER BY pr.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Product reviews</h1><p>Moderate customer ratings and product feedback.</p></div>
    <div class="admin-shell">
      <?php include __DIR__ . '/nav.php'; ?>
      <section class="admin-main panel">
        <?php if ($notice): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
        <?php if ($reviews): ?>
          <table>
            <thead><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Comment</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach ($reviews as $review): ?>
                <tr>
                  <td><strong><?= e($review['product_name']) ?></strong><br><small><?= e($review['brand']) ?> · Order #<?= (int) $review['order_id'] ?></small></td>
                  <td><?= e($review['full_name']) ?><br><small><?= e($review['email']) ?></small></td>
                  <td><span class="rating"><?= (int) $review['rating'] ?> / 5</span></td>
                  <td><?= nl2br(e($review['comment'])) ?><br><small><?= e($review['created_at']) ?></small></td>
                  <td><span class="badge <?= e($review['status']) ?>"><?= e($review['status']) ?></span></td>
                  <td>
                    <form method="post" class="inline-form review-admin-actions">
                      <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
                      <?php if ($review['status'] === 'visible'): ?>
                        <button class="btn secondary" name="action" value="hide" type="submit">Hide</button>
                      <?php else: ?>
                        <button class="btn secondary" name="action" value="unhide" type="submit">Unhide</button>
                      <?php endif; ?>
                      <button class="btn ghost" name="action" value="delete" type="submit">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-state">
            <h2>No reviews yet</h2>
            <p>Verified customer product reviews will appear here after purchases.</p>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
