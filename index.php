<?php
$page_title = 'NovaTech Gadgets | Premium Gadget Store';
$active = 'landing';
$landing_page = true;
$hide_chatbot = true;
require_once __DIR__ . '/includes/header.php';

$heroProducts = $pdo->query("SELECT * FROM products WHERE status = 'active' AND image IS NOT NULL AND image <> '' ORDER BY rating DESC, product_id DESC LIMIT 4")->fetchAll();
$previewProducts = $pdo->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON c.category_id = p.category_id WHERE p.status = 'active' ORDER BY rating DESC, p.product_id DESC LIMIT 4")->fetchAll();
$eventCampaigns = [
    [
        'key' => '8.8',
        'title' => '8.8 Mega Gadget Sale',
        'badge' => 'Limited Time',
        'subtitle' => 'Smart deals on gadgets for study, work, and lifestyle.',
        'chips' => ['Up to 30% Off', 'Limited Time', 'Selected Gadgets Only'],
        'button' => 'Shop 8.8 Deals',
        'image' => 'assets/images/events/8.8_gadget_sale_promo_banner.png',
        'link' => 'products.php?event=8.8',
        'start' => '2026-08-01',
        'end' => '2026-08-08',
        'accent' => 'blue',
    ],
    [
        'key' => 'merdeka',
        'title' => 'Merdeka Tech Deals',
        'badge' => 'Merdeka Special',
        'subtitle' => 'Celebrate Malaysia National Day with special gadget offers.',
        'chips' => ['Merdeka Special', 'Limited Time', 'Selected Gadgets Only'],
        'button' => 'Explore Merdeka Deals',
        'image' => 'assets/images/events/merdeka_tech_deals_celebration.png',
        'link' => 'products.php?event=merdeka',
        'start' => '2026-08-24',
        'end' => '2026-08-31',
        'accent' => 'emerald',
    ],
    [
        'key' => '9.9',
        'title' => '9.9 Super Gadget Sale',
        'badge' => 'Limited Time',
        'subtitle' => 'Exclusive tech deals for a limited time only.',
        'chips' => ['Big Savings', 'Limited Time', 'Top Picks'],
        'button' => 'Shop 9.9 Deals',
        'image' => 'assets/images/events/9.9_gadget_sale_promotion.png',
        'link' => 'products.php?event=9.9',
        'start' => '2026-09-01',
        'end' => '2026-09-09',
        'accent' => 'sky',
    ],
    [
        'key' => '10.10',
        'title' => '10.10 Tech Festival',
        'badge' => 'Limited Time',
        'subtitle' => 'Upgrade your setup with special savings this 10.10.',
        'chips' => ['Hot Picks', 'Best Value', 'Limited Time'],
        'button' => 'Explore 10.10 Offers',
        'image' => 'assets/images/events/10.10_tech_festival_sale_banner.png',
        'link' => 'products.php?event=10.10',
        'start' => '2026-10-01',
        'end' => '2026-10-10',
        'accent' => 'violet',
    ],
    [
        'key' => '11.11',
        'title' => '11.11 Mega Deals',
        'badge' => 'One Day Only',
        'subtitle' => 'Do not miss the biggest gadget savings of the season.',
        'chips' => ['Mega Savings', 'One Day Only', 'Best Sellers'],
        'button' => 'Shop 11.11 Deals',
        'image' => 'assets/images/events/11.11_mega_deals_promotion_banner.png',
        'link' => 'products.php?event=11.11',
        'start' => '2026-11-01',
        'end' => '2026-11-11',
        'accent' => 'graphite',
    ],
    [
        'key' => '12.12',
        'title' => '12.12 Year-End Sale',
        'badge' => 'Limited Time',
        'subtitle' => 'Finish the year with premium gadgets at great prices.',
        'chips' => ['Year-End Deals', 'Final Chance', 'Selected Gadgets Only'],
        'button' => 'Shop 12.12 Deals',
        'image' => 'assets/images/events/12.12_year_end_gadget_sale_banner.png',
        'link' => 'products.php?event=12.12',
        'start' => '2026-12-01',
        'end' => '2026-12-12',
        'accent' => 'silver',
    ],
    [
        'key' => 'christmas',
        'title' => 'Christmas Gadget Sale',
        'badge' => 'Christmas Special',
        'subtitle' => 'Celebrate the season with premium gadgets and festive savings.',
        'chips' => ['Holiday Deals', 'Gift Picks', 'Limited Time'],
        'button' => 'Shop Christmas Deals',
        'image' => 'assets/images/events/christmas_gadget_sale_banner_design.png',
        'link' => 'products.php?event=christmas',
        'start' => '2026-12-18',
        'end' => '2026-12-25',
        'accent' => 'red',
    ],
];

$today = new DateTimeImmutable('today');
$initialEventIndex = 0;
$bestDistance = PHP_INT_MAX;
foreach ($eventCampaigns as $index => $event) {
    $start = new DateTimeImmutable($event['start']);
    $end = new DateTimeImmutable($event['end']);
    if ($today >= $start && $today <= $end) {
        $initialEventIndex = $index;
        break;
    }
    $distance = $start >= $today ? (int) $today->diff($start)->format('%a') : 100000 + (int) $end->diff($today)->format('%a');
    if ($distance < $bestDistance) {
        $bestDistance = $distance;
        $initialEventIndex = $index;
    }
}
?>
<main class="landing-page">
  <section class="landing-hero">
    <div class="container landing-hero-grid">
      <div class="landing-copy">
        <h1>Discover Smart Gadgets for Modern Living</h1>
        <p>Explore premium devices designed for study, work, entertainment, and everyday comfort.</p>
        <div class="landing-actions">
          <a class="btn" href="login.php">Login / Get Started</a>
          <form method="post" action="login.php" class="guest-entry-form">
            <input type="hidden" name="action" value="guest">
            <button class="btn secondary" type="submit">Continue as Guest</button>
          </form>
        </div>
        <div class="landing-proof">
          <span>Secure OTP checkout</span>
          <span>Warranty records included</span>
          <span>Admin-approved product requests</span>
        </div>
      </div>
      <div class="landing-product-stage" aria-label="Premium gadget product showcase">
        <?php foreach (array_slice($heroProducts, 0, 4) as $index => $product): ?>
          <a class="landing-float-card float-<?= $index + 1 ?>" href="product-detail.php?id=<?= (int) $product['product_id'] ?>">
            <div class="landing-float-image"><?= product_visual($product) ?></div>
            <strong><?= e($product['product_name']) ?></strong>
            <span><?= money($product['price']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="landing-section event-section" aria-labelledby="eventCampaignTitle">
    <div class="container">
      <div class="landing-section-head event-header">
        <span class="event-kicker">Seasonal promotions</span>
        <h2 id="eventCampaignTitle">Current Tech Campaigns</h2>
        <p>Explore limited-time gadget promotions and seasonal offers.</p>
      </div>
      <div class="event-carousel" data-event-carousel data-initial-slide="<?= (int) $initialEventIndex ?>">
        <div class="event-track">
          <?php foreach ($eventCampaigns as $index => $event): ?>
            <?php $imagePath = __DIR__ . '/' . $event['image']; ?>
            <article class="event-slide <?= $index === $initialEventIndex ? 'active' : '' ?>" data-event-slide>
              <a class="event-banner accent-<?= e($event['accent']) ?>" href="<?= e($event['link']) ?>" aria-label="<?= e($event['title']) ?>">
                <?php if (is_file($imagePath)): ?>
                  <img class="event-banner-image" src="<?= e($event['image']) ?>" alt="<?= e($event['title']) ?>">
                <?php else: ?>
                  <div class="event-banner-fallback">
                    <div class="event-copy">
                      <span class="event-badge"><?= e($event['badge']) ?></span>
                      <h3><?= e($event['title']) ?></h3>
                      <p><?= e($event['subtitle']) ?></p>
                      <div class="event-chips">
                        <?php foreach ($event['chips'] as $chip): ?>
                          <span><?= e($chip) ?></span>
                        <?php endforeach; ?>
                      </div>
                      <span class="btn event-cta"><?= e($event['button']) ?></span>
                    </div>
                    <div class="event-visual" aria-hidden="true">
                      <span></span>
                      <span></span>
                      <span></span>
                    </div>
                  </div>
                <?php endif; ?>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
        <button class="event-arrow event-prev" type="button" data-event-prev aria-label="Previous event">&lsaquo;</button>
        <button class="event-arrow event-next" type="button" data-event-next aria-label="Next event">&rsaquo;</button>
        <div class="event-dots" role="tablist" aria-label="Event promotion slides">
          <?php foreach ($eventCampaigns as $index => $event): ?>
            <button class="event-dot <?= $index === $initialEventIndex ? 'active' : '' ?>" type="button" data-event-dot="<?= $index ?>" aria-label="Show <?= e($event['title']) ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="landing-section landing-soft">
    <div class="container">
      <div class="landing-section-head">
        <h2>Built for a trustworthy gadget buying experience</h2>
        <p>NovaTech combines product discovery, secure payment verification, warranty clarity, and admin-controlled product requests in one polished store flow.</p>
      </div>
      <div class="landing-benefit-grid">
        <article class="landing-benefit"><span>01</span><h3>Premium Gadget Collection</h3><p>Curated phones, laptops, tablets, watches, audio, accessories, and lifestyle tech for study and work.</p></article>
        <article class="landing-benefit"><span>02</span><h3>Secure OTP Payment</h3><p>Checkout keeps payment verification clear with OTP confirmation and invoice email flow.</p></article>
        <article class="landing-benefit"><span>03</span><h3>Warranty Included</h3><p>Invoice records keep product warranty period, type, coverage, and purchase date snapshots.</p></article>
        <article class="landing-benefit"><span>04</span><h3>AI Gadget Finder</h3><p>Customers can ask for gadgets, browse matching products, or request unavailable products for admin approval.</p></article>
      </div>
    </div>
  </section>

  <section class="landing-section">
    <div class="container">
      <div class="landing-section-head landing-split-head">
        <div><h2>Preview the collection</h2><p>A quick look at popular products before you enter the full store.</p></div>
        <a class="btn secondary" href="products.php">Explore Products</a>
      </div>
      <div class="landing-preview-grid">
        <?php foreach ($previewProducts as $product): ?>
          <article class="landing-preview-card">
            <a class="landing-preview-image" href="product-detail.php?id=<?= (int) $product['product_id'] ?>"><?= product_visual($product) ?></a>
            <span><?= e($product['category_name'] ?? $product['brand']) ?></span>
            <h3><?= e($product['product_name']) ?></h3>
            <p>Starting from <?= money($product['price']) ?></p>
            <a class="btn ghost" href="product-detail.php?id=<?= (int) $product['product_id'] ?>">View Product</a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="landing-section landing-ai-section">
    <div class="container landing-ai-card">
      <div>
        <h2>NovaTech AI Gadget Finder</h2>
        <p>Use the AI Assistant to search existing products or request unavailable gadgets. New product requests are sent to admin first, so the catalog stays controlled and trustworthy.</p>
      </div>
      <a class="btn" href="login.php">Start with NovaTech</a>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
