<?php
$page_title = 'Wishlist | NovaTech Gadgets';
$active = 'wishlist';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/promotions.php';
require_once __DIR__ . '/includes/wishlist.php';
require_customer();

$customerId = (int) $_SESSION['user']['user_id'];
ensure_wishlist_schema($pdo);
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $productStmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON c.category_id = p.category_id WHERE p.product_id = ? AND p.status = 'active'");
    $productStmt->execute([$productId]);
    $product = $productStmt->fetch();

    if ($product) {
        if ($action === 'remove') {
            remove_from_wishlist($pdo, $customerId, $productId);
            $notice = 'Product removed from wishlist.';
        }

        if ($action === 'move_to_cart') {
            $pricing = discounted_product_price($pdo, $product, (float) $product['price']);
            $check = $pdo->prepare('SELECT cart_id FROM cart WHERE user_id = ? AND product_id = ? AND COALESCE(selected_color, "") = "" AND COALESCE(selected_spec, "") = ""');
            $check->execute([$customerId, $productId]);
            $cartItem = $check->fetch();

            if ($cartItem) {
                $stmt = $pdo->prepare('UPDATE cart SET quantity = quantity + 1, unit_price = ? WHERE cart_id = ?');
                $stmt->execute([$pricing['price'], $cartItem['cart_id']]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity, selected_color, selected_spec, unit_price) VALUES (?, ?, 1, "", "", ?)');
                $stmt->execute([$customerId, $productId, $pricing['price']]);
            }

            remove_from_wishlist($pdo, $customerId, $productId);
            redirect('cart.php');
        }
    }
}

$items = wishlist_items($pdo, $customerId);

require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Wishlist</h1><p>Saved NovaTech gadgets you may want to buy later.</p></div>
    <?php if ($notice): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
    <?php if ($items): ?>
      <div class="wishlist-grid">
        <?php foreach ($items as $item): ?>
          <?php $pricing = discounted_product_price($pdo, $item, (float) $item['price']); ?>
          <article class="panel wishlist-item">
            <a class="wishlist-image" href="product-detail.php?id=<?= (int) $item['product_id'] ?>"><?= product_visual($item) ?></a>
            <div class="wishlist-body">
              <span class="badge"><?= e($item['brand']) ?></span>
              <h2><?= e($item['product_name']) ?></h2>
              <p><?= e($item['category_name'] ?? 'Gadgets') ?></p>
              <div class="price-stack">
                <?php if ($pricing['discount']): ?>
                  <del><?= money($pricing['original']) ?></del>
                  <strong><?= money($pricing['price']) ?></strong>
                  <small class="discount-badge">Save <?= money($pricing['save']) ?></small>
                <?php else: ?>
                  <strong><?= money($item['price']) ?></strong>
                <?php endif; ?>
              </div>
              <p><strong>Warranty:</strong> <?= e($item['warranty_period'] ?? '1 Year') ?> <?= e($item['warranty_type'] ?? 'Manufacturer Warranty') ?></p>
              <p><strong>Stock:</strong> <?= (int) $item['stock_quantity'] > 0 ? 'In stock' : 'Out of stock' ?></p>
              <div class="wishlist-actions">
                <form method="post">
                  <input type="hidden" name="action" value="move_to_cart">
                  <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                  <button class="btn" type="submit" <?= (int) $item['stock_quantity'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
                </form>
                <form method="post">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                  <button class="btn secondary" type="submit">Remove</button>
                </form>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <h2>Your wishlist is empty</h2>
        <p>Save products you like and come back later when you are ready to buy.</p>
        <a class="btn" href="products.php">Browse Products</a>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
