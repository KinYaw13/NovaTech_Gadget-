<?php
$page_title = 'Products | NovaTech Gadgets';
$active = 'products';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/promotions.php';
ensure_customer_engagement_schema($pdo);

$search = trim($_GET['search'] ?? '');
$category = (int) ($_GET['category'] ?? 0);
$brand = trim((string) ($_GET['brand'] ?? ''));
$warranty = trim((string) ($_GET['warranty'] ?? ''));
$discountFilter = trim((string) ($_GET['discount'] ?? ''));
$stockStatus = trim((string) ($_GET['stock'] ?? ''));
$minPrice = trim((string) ($_GET['min_price'] ?? ''));
$maxPrice = trim((string) ($_GET['max_price'] ?? ''));
$sort = $_GET['sort'] ?? 'newest';
$categories = $pdo->query('SELECT * FROM categories ORDER BY category_name')->fetchAll();
$brands = $pdo->query("SELECT DISTINCT brand FROM products WHERE status = 'active' AND brand <> '' ORDER BY brand")->fetchAll();
$warranties = $pdo->query("SELECT DISTINCT warranty_period FROM products WHERE status = 'active' AND warranty_period <> '' ORDER BY warranty_period")->fetchAll();

$sql = "SELECT p.*, c.category_name FROM products p JOIN categories c ON c.category_id = p.category_id WHERE p.status = 'active'";
$params = [];
if ($search !== '') {
    $sql .= ' AND (p.product_name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)';
    $like = "%{$search}%";
    array_push($params, $like, $like, $like);
}
if ($category > 0) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $category;
}
if ($brand !== '') {
    $sql .= ' AND p.brand = ?';
    $params[] = $brand;
}
if ($warranty !== '') {
    $sql .= ' AND p.warranty_period = ?';
    $params[] = $warranty;
}
if ($stockStatus === 'in_stock') {
    $sql .= ' AND p.stock_quantity > 0';
} elseif ($stockStatus === 'out_stock') {
    $sql .= ' AND p.stock_quantity <= 0';
}
$sql .= ' ORDER BY p.created_at DESC, p.product_id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$products = array_values(array_filter(array_map(function (array $product) use ($pdo): ?array {
    $pricing = discounted_product_price($pdo, $product, (float) $product['price']);
    $product['_effective_price'] = $pricing['price'];
    $product['_discount_save'] = $pricing['save'];
    $product['_has_discount'] = $pricing['discount'] !== null;
    return $product;
}, $products), function (array $product) use ($minPrice, $maxPrice, $discountFilter): bool {
    $price = (float) $product['_effective_price'];
    if ($minPrice !== '' && $price < (float) $minPrice) {
        return false;
    }
    if ($maxPrice !== '' && $price > (float) $maxPrice) {
        return false;
    }
    if ($discountFilter === 'discounted' && !$product['_has_discount']) {
        return false;
    }
    if ($discountFilter === 'regular' && $product['_has_discount']) {
        return false;
    }
    return true;
}));

usort($products, function (array $a, array $b) use ($sort): int {
    return match ($sort) {
        'price_low' => $a['_effective_price'] <=> $b['_effective_price'],
        'price_high' => $b['_effective_price'] <=> $a['_effective_price'],
        'best_discount' => $b['_discount_save'] <=> $a['_discount_save'],
        'newest' => strcmp((string) $b['created_at'], (string) $a['created_at']) ?: ((int) $b['product_id'] <=> (int) $a['product_id']),
        default => strcasecmp((string) $a['product_name'], (string) $b['product_name']),
    };
});
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Shop gadgets</h1><p>Search, filter, and sort products from the database.</p></div>
    <form class="filters product-filter-panel" method="get">
      <div class="field"><label>Search</label><input name="search" value="<?= e($search) ?>" placeholder="Search products"></div>
      <div class="field"><label>Category</label><select name="category">
          <option value="0">All categories</option>
          <?php foreach ($categories as $item): ?>
            <option value="<?= (int) $item['category_id'] ?>" <?= $category === (int) $item['category_id'] ? 'selected' : '' ?>><?= e($item['category_name']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="field"><label>Brand</label><select name="brand">
          <option value="">All brands</option>
          <?php foreach ($brands as $item): ?>
            <option value="<?= e($item['brand']) ?>" <?= $brand === $item['brand'] ? 'selected' : '' ?>><?= e($item['brand']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="field price-range-fields"><label>Price range</label><div><input name="min_price" type="number" min="0" step="1" value="<?= e($minPrice) ?>" placeholder="Min RM"><input name="max_price" type="number" min="0" step="1" value="<?= e($maxPrice) ?>" placeholder="Max RM"></div></div>
      <div class="field"><label>Warranty</label><select name="warranty">
          <option value="">All warranty</option>
          <?php foreach ($warranties as $item): ?>
            <option value="<?= e($item['warranty_period']) ?>" <?= $warranty === $item['warranty_period'] ? 'selected' : '' ?>><?= e($item['warranty_period']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="field"><label>Discount</label><select name="discount">
          <option value="">All products</option>
          <option value="discounted" <?= $discountFilter === 'discounted' ? 'selected' : '' ?>>Discounted only</option>
          <option value="regular" <?= $discountFilter === 'regular' ? 'selected' : '' ?>>Regular price only</option>
        </select></div>
      <div class="field"><label>Stock status</label><select name="stock">
          <option value="">All stock</option>
          <option value="in_stock" <?= $stockStatus === 'in_stock' ? 'selected' : '' ?>>In stock</option>
          <option value="out_stock" <?= $stockStatus === 'out_stock' ? 'selected' : '' ?>>Out of stock</option>
        </select></div>
      <div class="field"><label>Sort</label><select name="sort">
          <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
          <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price low to high</option>
          <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price high to low</option>
          <option value="best_discount" <?= $sort === 'best_discount' ? 'selected' : '' ?>>Best discount</option>
        </select></div>
      <div class="filter-actions"><button class="btn" type="submit">Apply Filters</button><a class="btn secondary" href="products.php">Reset</a></div>
    </form>
    <p><span class="badge"><?= count($products) ?> products</span></p>
    <?php if ($products): ?>
      <div class="grid">
        <?php foreach ($products as $product): ?>
          <?php include __DIR__ . '/includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <h2>No products found</h2>
        <p>Try changing the search keyword, category, price range, warranty, discount, or stock filter.</p>
        <a class="btn secondary" href="products.php">Reset Filters</a>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
