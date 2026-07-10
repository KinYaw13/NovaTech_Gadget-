<?php
$page_title = 'Cart | NovaTech Gadgets';
$active = 'products';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/promotions.php';
require_login();
ensure_customer_engagement_schema($pdo);

$userId = (int) $_SESSION['user']['user_id'];
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $productStmt = $pdo->prepare('SELECT p.*, cat.category_name FROM products p LEFT JOIN categories cat ON cat.category_id = p.category_id WHERE p.product_id = ? AND p.status = "active"');
        $productStmt->execute([$productId]);
        $product = $productStmt->fetch();

        if (!$product) {
            redirect('products.php');
        }

        $selectedColor = trim($_POST['selected_color'] ?? '');
        $selectedSpec = trim($_POST['selected_spec'] ?? '');
        $variantPrice = product_variant_price($product['product_name'], $selectedSpec, (float) $product['price']);
        $unitPrice = discounted_product_price($pdo, $product, $variantPrice)['price'];

        $check = $pdo->prepare('SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND COALESCE(selected_color, "") = ? AND COALESCE(selected_spec, "") = ?');
        $check->execute([$userId, $productId, $selectedColor, $selectedSpec]);
        $item = $check->fetch();
        if ($item) {
            $stmt = $pdo->prepare('UPDATE cart SET quantity = quantity + ?, unit_price = ? WHERE cart_id = ?');
            $stmt->execute([$quantity, $unitPrice, $item['cart_id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity, selected_color, selected_spec, unit_price) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $productId, $quantity, $selectedColor, $selectedSpec, $unitPrice]);
        }
    }

    if ($action === 'update') {
        $cartId = (int) ($_POST['cart_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $stmt = $pdo->prepare('UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?');
        $stmt->execute([$quantity, $cartId, $userId]);
    }

    if ($action === 'remove') {
        $cartId = (int) ($_POST['cart_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM cart WHERE cart_id = ? AND user_id = ?');
        $stmt->execute([$cartId, $userId]);
    }

    redirect('cart.php');
}

$stmt = $pdo->prepare('SELECT c.cart_id, c.quantity, c.selected_color, c.selected_spec, c.unit_price, p.*, cat.category_name FROM cart c JOIN products p ON p.product_id = c.product_id LEFT JOIN categories cat ON cat.category_id = p.category_id WHERE c.user_id = ? ORDER BY c.created_at DESC');
$stmt->execute([$userId]);
$items = $stmt->fetchAll();
$items = array_map(function (array $item) use ($pdo): array {
    $variantPrice = product_variant_price($item['product_name'], (string) ($item['selected_spec'] ?? ''), (float) $item['price']);
    $item['unit_price'] = discounted_product_price($pdo, $item, $variantPrice)['price'];
    return $item;
}, $items);
$subtotal = array_reduce($items, fn($sum, $item) => $sum + ((float) ($item['unit_price'] ?? $item['price']) * (int) $item['quantity']), 0.0);
$shipping = $subtotal > 0 ? 15.00 : 0.00;
$tax = $subtotal * 0.06;
$total = $subtotal + $shipping + $tax;

require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Shopping cart</h1><p>Update quantities, remove items, and review your order summary.</p></div>
    <div class="cart-layout">
      <div class="cart-items">
        <?php if (!$items): ?>
          <div class="empty-state">
            <h2>Your cart is empty</h2>
            <p>Start exploring NovaTech gadgets and add your favorite devices.</p>
            <a class="btn" href="products.php">Browse Products</a>
          </div>
        <?php else: ?>
          <div class="panel">
            <table>
              <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($items as $item): ?>
                  <tr>
                    <?php $selectedOptions = trim(($item['selected_spec'] ?? '') . ' ' . ($item['selected_color'] ?? '')); ?>
                    <td><strong><?= e($item['product_name']) ?></strong><br><span class="badge"><?= e($item['brand']) ?></span><?php if ($selectedOptions !== ''): ?><br><small><?= e($selectedOptions) ?></small><?php endif; ?></td>
                    <td>
                      <form class="inline-form" method="post">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="cart_id" value="<?= (int) $item['cart_id'] ?>">
                        <input name="quantity" type="number" min="1" value="<?= (int) $item['quantity'] ?>">
                        <button class="btn secondary" type="submit">Update</button>
                      </form>
                    </td>
                    <td><?= money($item['unit_price'] ?? $item['price']) ?></td>
                    <td><?= money((float) ($item['unit_price'] ?? $item['price']) * (int) $item['quantity']) ?></td>
                    <td>
                      <form method="post">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="cart_id" value="<?= (int) $item['cart_id'] ?>">
                        <button class="btn ghost" type="submit">Remove</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
      <aside class="summary panel">
        <h2>Order summary</h2>
        <div class="summary-row"><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div>
        <div class="summary-row"><span>Shipping</span><strong><?= money($shipping) ?></strong></div>
        <div class="summary-row"><span>Estimated tax</span><strong><?= money($tax) ?></strong></div>
        <div class="summary-row total"><span>Total</span><span><?= money($total) ?></span></div>
        <a class="btn" href="checkout.php">Proceed to Checkout</a>
      </aside>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
