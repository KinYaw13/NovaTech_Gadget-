<?php
require_once __DIR__ . '/promotions.php';
require_once __DIR__ . '/wishlist.php';
$cardPrice = discounted_product_price($pdo, $product, (float) $product['price']);
$cardUser = current_user();
$cardRole = $cardUser['role'] ?? 'visitor';
$cardInWishlist = $cardRole === 'customer' ? is_in_wishlist($pdo, (int) $cardUser['user_id'], (int) $product['product_id']) : false;
?>
<article class="product-card">
  <a class="product-image" href="product-detail.php?id=<?= (int) $product['product_id'] ?>">
    <?= product_visual($product) ?>
  </a>
  <div class="product-body">
    <span class="badge"><?= e($product['brand']) ?></span>
    <h3><?= e($product['product_name']) ?></h3>
    <p><?= e($product['description']) ?></p>
    <div class="product-meta">
      <span class="price-stack">
        <?php if ($cardPrice['discount']): ?>
          <del><?= money($cardPrice['original']) ?></del>
          <strong><?= money($cardPrice['price']) ?></strong>
          <small class="discount-badge">Save <?= money($cardPrice['save']) ?></small>
        <?php else: ?>
          <?= money($product['price']) ?>
        <?php endif; ?>
      </span>
      <span class="rating"><?= e((string) $product['rating']) ?> / 5</span>
    </div>
    <div class="card-actions">
      <form action="cart.php" method="post">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
        <button class="btn" type="submit">Add to Cart</button>
      </form>
      <a class="btn secondary" href="product-detail.php?id=<?= (int) $product['product_id'] ?>">View Details</a>
      <?php if ($cardRole === 'customer'): ?>
        <form action="wishlist_action.php" method="post" class="span-action">
          <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
          <input type="hidden" name="return_to" value="<?= e($_SERVER['REQUEST_URI'] ?? 'products.php') ?>">
          <button class="btn ghost wishlist-save-btn" name="action" value="<?= $cardInWishlist ? 'remove' : 'add' ?>" type="submit"><?= $cardInWishlist ? 'Saved' : 'Save' ?></button>
        </form>
      <?php else: ?>
        <a class="btn ghost span-action wishlist-save-btn" href="login.php">Save</a>
      <?php endif; ?>
    </div>
  </div>
</article>
