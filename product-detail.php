<?php
$page_title = 'Product Detail | NovaTech Gadgets';
$active = 'products';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/promotions.php';
require_once __DIR__ . '/includes/reviews.php';
require_once __DIR__ . '/includes/wishlist.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p JOIN categories c ON c.category_id = p.category_id WHERE p.product_id = ? AND p.status = 'active'");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    redirect('products.php');
}

ensure_product_reviews_schema($pdo);
$reviewNotice = '';
$reviewError = '';
$user = current_user();
$isReviewCustomer = ($user['role'] ?? '') === 'customer';
$customerId = $isReviewCustomer ? (int) $user['user_id'] : 0;
$reviewableItems = $isReviewCustomer ? reviewable_order_items($pdo, $customerId, (int) $product['product_id']) : [];
$inWishlist = $isReviewCustomer ? is_in_wishlist($pdo, $customerId, (int) $product['product_id']) : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_review') {
    if (!$isReviewCustomer) {
        $reviewError = 'Please login as a customer before leaving a review.';
    } else {
        $orderItemId = (int) ($_POST['order_item_id'] ?? 0);
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = trim((string) ($_POST['comment'] ?? ''));
        $allowedItems = array_column($reviewableItems, 'order_id', 'order_item_id');

        if (!isset($allowedItems[$orderItemId])) {
            $reviewError = 'Only purchased products can be reviewed once per order item.';
        } elseif ($rating < 1 || $rating > 5) {
            $reviewError = 'Please choose a rating from 1 to 5 stars.';
        } elseif ($comment === '') {
            $reviewError = 'Please enter a short review comment.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO product_reviews (product_id, customer_id, order_id, order_item_id, rating, comment) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([(int) $product['product_id'], $customerId, (int) $allowedItems[$orderItemId], $orderItemId, $rating, $comment]);
            redirect('product-detail.php?id=' . (int) $product['product_id'] . '&review=added');
        }
    }

    $reviewableItems = $isReviewCustomer ? reviewable_order_items($pdo, $customerId, (int) $product['product_id']) : [];
}

if (($_GET['review'] ?? '') === 'added') {
    $reviewNotice = 'Thank you. Your review has been posted.';
}

$relatedStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND product_id <> ? AND status = 'active' LIMIT 4");
$relatedStmt->execute([$product['category_id'], $product['product_id']]);
$related = $relatedStmt->fetchAll();
$uploadedColorRows = product_uploaded_color_rows($pdo, (int) $product['product_id']);
$colors = $uploadedColorRows
    ? array_map(fn($row) => [$row['color_name'], $row['color_hex']], $uploadedColorRows)
    : product_colors($product['product_name']);
$specs = product_specs($product['product_name']);
$variants = product_variants($product['product_name'], (float) $product['price']);
$pricedVariants = array_map(function (array $variant) use ($pdo, $product): array {
    $pricing = discounted_product_price($pdo, $product, (float) $variant[1]);
    return [$variant[0], $pricing['price'], $pricing['original'], $pricing['save'], $pricing['discount']];
}, $variants);
$colorImages = $uploadedColorRows
    ? array_column($uploadedColorRows, 'image', 'color_name')
    : product_color_images($product['product_name'], product_image_url($product));
$displayProduct = $product;
if ($colors && !empty($colorImages[$colors[0][0]])) {
    $displayProduct['image'] = $colorImages[$colors[0][0]];
}
$reviewSummary = product_review_summary($pdo, (int) $product['product_id']);
$visibleReviews = product_visible_reviews($pdo, (int) $product['product_id']);
$displayRating = $reviewSummary['count'] > 0 ? $reviewSummary['average'] : (float) $product['rating'];
require_once __DIR__ . '/includes/header.php';
?>
<main>
  <section class="section">
    <div class="container detail-layout">
      <div class="detail-media"><?= product_visual($displayProduct) ?></div>
      <div class="detail-info">
        <span class="badge"><?= e($product['category_name']) ?></span>
        <h1><?= e($product['product_name']) ?></h1>
        <p class="rating"><?= e((string) $displayRating) ?> / 5 rating · <?= (int) $reviewSummary['count'] ?> reviews</p>
        <h2 data-product-price>
          <?php if ($pricedVariants[0][4]): ?><del><?= money($pricedVariants[0][2]) ?></del> Now <?php endif; ?><?= money($pricedVariants[0][1]) ?>
        </h2>
        <?php if ($pricedVariants[0][4]): ?><span class="discount-badge">Save <?= money($pricedVariants[0][3]) ?></span><?php endif; ?>
        <p><?= e($product['description']) ?></p>
        <?php if ($variants): ?>
          <h3>Storage / spec</h3>
          <div class="option-row variant-options" data-variant-options>
            <?php foreach ($pricedVariants as $index => $variant): ?>
              <button class="option variant-option <?= $index === 0 ? 'selected' : '' ?>" type="button" data-variant-name="<?= e($variant[0]) ?>" data-variant-price="<?= e((string) $variant[1]) ?>">
                <span><?= e($variant[0]) ?></span><strong><?= money($variant[1]) ?></strong>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if ($colors): ?>
          <h3>Available market colors</h3>
          <div class="option-row color-options" data-color-options>
            <?php foreach ($colors as $index => $color): ?>
              <button class="option color-option <?= $index === 0 ? 'selected' : '' ?>" type="button" data-color-name="<?= e($color[0]) ?>" data-color-image="<?= e(($colorImages[$color[0]] ?? '') ?: product_image_url($product)) ?>">
                <span class="color-swatch" style="background: <?= e($color[1]) ?>"></span><?= e($color[0]) ?>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <form class="option-row" action="cart.php" method="post" data-product-purchase>
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
          <input type="hidden" name="selected_color" value="<?= e($colors[0][0] ?? '') ?>" data-selected-color>
          <input type="hidden" name="selected_spec" value="<?= e($variants[0][0]) ?>" data-selected-variant>
          <input type="hidden" name="selected_price" value="<?= e((string) $pricedVariants[0][1]) ?>" data-selected-price>
          <input name="quantity" type="number" min="1" max="<?= (int) $product['stock_quantity'] ?>" value="1" style="max-width:100px;">
          <button class="btn" type="submit">Add to Cart</button>
        </form>
        <div class="option-row">
          <?php if ($isReviewCustomer): ?>
            <form action="wishlist_action.php" method="post">
              <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
              <input type="hidden" name="return_to" value="product-detail.php?id=<?= (int) $product['product_id'] ?>">
              <button class="btn secondary wishlist-save-btn" name="action" value="<?= $inWishlist ? 'remove' : 'add' ?>" type="submit"><?= $inWishlist ? 'Remove from Wishlist' : 'Save to Wishlist' ?></button>
            </form>
          <?php else: ?>
            <a class="btn secondary wishlist-save-btn" href="login.php">Login to Save</a>
          <?php endif; ?>
        </div>
        <div class="panel">
          <h3>Product specifications</h3>
          <table>
            <tr><th>Brand</th><td><?= e($product['brand']) ?></td></tr>
            <tr><th>Category</th><td><?= e($product['category_name']) ?></td></tr>
            <tr><th>Stock</th><td><?= (int) $product['stock_quantity'] ?> units</td></tr>
            <?php foreach ($specs as $index => $spec): ?>
              <tr><th>Spec <?= $index + 1 ?></th><td><?= e($spec) ?></td></tr>
            <?php endforeach; ?>
            <tr><th>Warranty</th><td>1 year limited service demo</td></tr>
          </table>
        </div>
      </div>
    </div>
  </section>
  <section class="section soft">
    <div class="container">
      <div class="section-head"><div><h2>Customer reviews</h2><p>Reviews are only available from customers who purchased this product.</p></div></div>
      <div class="review-layout">
        <section class="panel review-form-panel">
          <?php if ($reviewNotice): ?><div class="notice success"><?= e($reviewNotice) ?></div><?php endif; ?>
          <?php if ($reviewError): ?><div class="notice error"><?= e($reviewError) ?></div><?php endif; ?>
          <?php if ($isReviewCustomer && $reviewableItems): ?>
            <h3>Leave a review</h3>
            <form method="post" class="review-form">
              <input type="hidden" name="action" value="add_review">
              <div class="field">
                <label>Purchased item</label>
                <select name="order_item_id" required>
                  <?php foreach ($reviewableItems as $item): ?>
                    <option value="<?= (int) $item['order_item_id'] ?>">Order #<?= (int) $item['order_id'] ?><?= !empty($item['selected_options']) ? ' - ' . e($item['selected_options']) : '' ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field">
                <label>Rating</label>
                <select name="rating" required>
                  <?php for ($stars = 5; $stars >= 1; $stars--): ?>
                    <option value="<?= $stars ?>"><?= $stars ?> star<?= $stars > 1 ? 's' : '' ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="field">
                <label>Comment</label>
                <textarea name="comment" rows="4" maxlength="1000" placeholder="Share your experience with this product..." required></textarea>
              </div>
              <button class="btn" type="submit">Submit Review</button>
            </form>
          <?php elseif ($isReviewCustomer): ?>
            <div class="empty-state compact">
              <h3>Review unavailable</h3>
              <p>You can review this product after purchasing it, or after using every purchased item review once.</p>
            </div>
          <?php else: ?>
            <div class="empty-state compact">
              <h3>Login to review</h3>
              <p>Only logged-in customers who purchased this product can leave a rating and comment.</p>
              <a class="btn secondary" href="login.php">Login</a>
            </div>
          <?php endif; ?>
        </section>
        <section class="review-list">
          <?php if ($visibleReviews): ?>
            <?php foreach ($visibleReviews as $review): ?>
              <article class="panel review-card-row">
                <div class="review-card-head">
                  <strong><?= e($review['full_name']) ?></strong>
                  <span class="rating"><?= (int) $review['rating'] ?> / 5 stars</span>
                </div>
                <p><?= nl2br(e($review['comment'])) ?></p>
                <small><?= e($review['created_at']) ?></small>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">
              <h2>No reviews yet</h2>
              <p>Customer reviews will appear here after verified purchasers submit them.</p>
            </div>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </section>
  <section class="section soft">
    <div class="container">
      <div class="section-head"><div><h2>Related products</h2><p>More products from the same setup family.</p></div></div>
      <div class="grid">
        <?php foreach ($related as $product): ?>
          <?php include __DIR__ . '/includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
