<?php
$page_title = 'Home | NovaTech Gadgets';
$active = 'home';
require_once __DIR__ . '/includes/header.php';

$featured = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY rating DESC LIMIT 4")->fetchAll();
$new = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC, product_id DESC LIMIT 4")->fetchAll();
$categories = $pdo->query('SELECT * FROM categories ORDER BY category_name')->fetchAll();
?>
<main>
  <section class="hero">
    <div class="container hero-grid">
      <div class="hero-copy">
        <h1>Discover Smart Gadgets for Modern Living</h1>
        <p>Minimal gadgets. Maximum performance. Explore original devices designed for study, work, entertainment, and everyday comfort.</p>
        <a class="btn" href="products.php">Shop Now</a>
      </div>
      <div class="hero-stage" aria-label="Premium gadget showcase">
        <div class="hero-showcase">
          <?php foreach (array_slice($featured, 0, 4) as $heroProduct): ?>
            <div class="hero-product-tile"><?= product_visual($heroProduct) ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="section soft">
    <div class="container">
      <div class="section-head">
        <div><h2>Featured gadgets</h2><p>Curated essentials with clean design and reliable performance.</p></div>
        <a class="btn secondary" href="products.php">View All</a>
      </div>
      <div class="grid">
        <?php foreach ($featured as $product): ?>
          <?php include __DIR__ . '/includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-head"><div><h2>Product categories</h2><p>Shop by device type and build your ideal setup.</p></div></div>
      <div class="grid">
        <?php foreach ($categories as $category): ?>
          <a class="category-card" href="products.php?category=<?= (int) $category['category_id'] ?>">
            <h3><?= e($category['category_name']) ?></h3>
            <p><?= e($category['description']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section soft">
    <div class="container">
      <div class="section-head"><div><h2>New arrivals</h2><p>Fresh gadget drops with original NovaTech styling.</p></div></div>
      <div class="grid">
        <?php foreach ($new as $product): ?>
          <?php include __DIR__ . '/includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="grid">
        <article class="info-card"><h3>Premium original UI</h3><p>Clean Apple Store inspired feeling without copying any Apple branding or product names.</p></article>
        <article class="info-card"><h3>Database powered</h3><p>Products, users, cart, checkout, and orders are stored through MySQL.</p></article>
        <article class="info-card"><h3>Admin ready</h3><p>Manage products, orders, customers, and dashboard stats from a protected admin area.</p></article>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>