<?php
$page_title = 'Admin Products | NovaTech Gadgets';
$active = 'admin';
$base = '../';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['product_id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE product_id = ?");
    $stmt->execute([$id]);
    redirect('products.php');
}

$products = $pdo->query('SELECT p.*, c.category_name FROM products p JOIN categories c ON c.category_id = p.category_id ORDER BY p.created_at DESC')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Product management</h1><p>Add, edit, and delete products.</p></div>
    <div class="admin-shell">
      <?php include __DIR__ . '/nav.php'; ?>
      <section class="admin-main panel">
        <div class="section-head"><div><h2>Products</h2><p><?= count($products) ?> records</p></div><a class="btn" href="add-product.php">Add Product</a></div>
        <table>
          <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <tr>
                <td><strong><?= e($product['product_name']) ?></strong><br><?= e($product['brand']) ?></td>
                <td><?= e($product['category_name']) ?></td>
                <td><?= money($product['price']) ?></td>
                <td><?= (int) $product['stock_quantity'] ?></td>
                <td><span class="badge"><?= e($product['status']) ?></span></td>
                <td>
                  <a class="btn secondary" href="edit-product.php?id=<?= (int) $product['product_id'] ?>">Edit</a>
                  <form class="inline-form" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
                    <button class="btn ghost" type="submit">Delete</button>
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

