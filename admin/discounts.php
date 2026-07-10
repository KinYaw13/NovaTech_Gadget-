<?php
$page_title = 'Discounts | NovaTech Gadgets';
$active = 'admin';
$base = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/promotions.php';
require_admin();
ensure_customer_engagement_schema($pdo);

$error = '';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['discount_name'] ?? ''));
    $type = $_POST['discount_type'] ?? 'percentage';
    $value = (float) ($_POST['discount_value'] ?? 0);
    $appliesTo = $_POST['applies_to'] ?? 'all';
    $category = trim((string) ($_POST['category'] ?? ''));
    $productId = (int) ($_POST['product_id'] ?? 0);
    $start = trim((string) ($_POST['start_date'] ?? ''));
    $end = trim((string) ($_POST['end_date'] ?? ''));
    $status = $_POST['status'] ?? 'active';

    if ($name === '' || $value <= 0) {
        $error = 'Please enter a discount name and value.';
    } elseif (!in_array($type, ['percentage', 'fixed'], true) || !in_array($appliesTo, ['all', 'category', 'product'], true) || !in_array($status, ['active', 'inactive', 'expired'], true)) {
        $error = 'Please choose valid discount options.';
    } elseif ($appliesTo === 'category' && $category === '') {
        $error = 'Please choose a category.';
    } elseif ($appliesTo === 'product' && $productId <= 0) {
        $error = 'Please choose a product.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO discounts (discount_name, discount_type, discount_value, applies_to, category, product_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $type, $value, $appliesTo, $appliesTo === 'category' ? $category : null, $appliesTo === 'product' ? $productId : null, $start !== '' ? $start : null, $end !== '' ? $end : null, $status]);
        $notice = 'Discount created.';
    }
}

$categories = $pdo->query('SELECT category_name FROM categories ORDER BY category_name')->fetchAll();
$products = $pdo->query('SELECT p.product_id, p.product_name, c.category_name FROM products p JOIN categories c ON c.category_id = p.category_id ORDER BY c.category_name, p.product_name')->fetchAll();
$discounts = $pdo->query('SELECT d.*, p.product_name FROM discounts d LEFT JOIN products p ON p.product_id = d.product_id ORDER BY d.created_at DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Discount management</h1><p>Create all-product, category, or product-specific discounts.</p></div>
    <div class="admin-shell">
      <?php include __DIR__ . '/nav.php'; ?>
      <section class="admin-main">
        <?php if ($notice): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <form class="panel admin-form" method="post">
          <div class="form-grid">
            <div class="field"><label>Discount name</label><input name="discount_name" required></div>
            <div class="field"><label>Discount type</label><select name="discount_type"><option value="percentage">Percentage</option><option value="fixed">Fixed amount</option></select></div>
            <div class="field"><label>Value</label><input name="discount_value" type="number" min="0.01" step="0.01" required></div>
            <div class="field"><label>Applies to</label><select name="applies_to"><option value="all">All products</option><option value="category">Category</option><option value="product">Product</option></select></div>
            <div class="field"><label>Category</label><select name="category" data-discount-category><option value="">Select category</option><?php foreach ($categories as $cat): ?><option value="<?= e($cat['category_name']) ?>"><?= e($cat['category_name']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label>Product</label><select name="product_id" data-discount-product><option value="0">Select product</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['product_id'] ?>" data-category="<?= e($product['category_name']) ?>"><?= e($product['product_name']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label>Start date</label><input name="start_date" type="datetime-local"></div>
            <div class="field"><label>End date</label><input name="end_date" type="datetime-local"></div>
            <div class="field"><label>Status</label><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
          </div>
          <button class="btn" type="submit">Create Discount</button>
        </form>
        <div class="panel" style="margin-top:24px">
          <h2>Discounts</h2>
          <table>
            <thead><tr><th>Name</th><th>Type</th><th>Applies</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($discounts as $discount): ?>
                <tr><td><?= e($discount['discount_name']) ?></td><td><?= e($discount['discount_type']) ?> <?= e((string) $discount['discount_value']) ?></td><td><?= e($discount['applies_to']) ?> <?= e($discount['category'] ?: ($discount['product_name'] ?? '')) ?></td><td><span class="badge"><?= e($discount['status']) ?></span></td></tr>
              <?php endforeach; ?>
              <?php if (!$discounts): ?>
                <tr><td class="empty-table-cell" colspan="4">
                  <div class="empty-state compact">
                    <h3>No discount available</h3>
                    <p>Create a product, category, or all-product discount and it will appear here.</p>
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
