<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/wishlist.php';

$page_title = $page_title ?? 'NovaTech Gadgets';
$active = $active ?? '';
$base = $base ?? '';
$user = current_user();
$role = $user['role'] ?? 'visitor';
$isLanding = !empty($landing_page);
$count = cart_count($pdo);
$homeHref = $base . 'index.php';
$currentScript = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$customerCounts = $role === 'customer' ? customer_notification_counts($pdo, (int) $user['user_id']) : ['unread_messages' => 0, 'active_vouchers' => 0, 'pending_warranty' => 0];
$adminCounts = $role === 'admin' ? admin_notification_counts($pdo) : ['unread_messages' => 0, 'pending_product_requests' => 0, 'pending_warranty' => 0, 'profile_completed' => 0];
$wishlistTotal = $role === 'customer' ? wishlist_count($pdo, (int) $user['user_id']) : 0;
$adminName = $role === 'admin' ? (string) ($user['full_name'] ?? 'NovaTech Admin') : '';
$adminActive = static fn(string $file): string => $currentScript === $file ? 'active' : '';
$adminMoreActive = in_array($currentScript, ['warranty-claims.php', 'vouchers.php', 'discounts.php', 'reviews.php', 'customers.php'], true) ? 'active' : '';

if ($isLanding) {
  $mobileLinks = [
    ['label' => 'Home', 'href' => $base . 'index.php'],
    ['label' => 'Products', 'href' => $base . 'products.php'],
    ['label' => 'Login', 'href' => $base . 'login.php'],
  ];
} elseif ($role === 'admin') {
  $mobileLinks = [
    ['label' => 'Dashboard', 'href' => $base . 'admin/dashboard.php'],
    ['label' => 'Products', 'href' => $base . 'admin/products.php'],
    ['label' => 'Orders', 'href' => $base . 'admin/orders.php'],
    ['label' => 'Requests' . nav_badge($adminCounts['pending_product_requests']), 'href' => $base . 'admin/product-requests.php'],
    ['label' => 'Messages' . nav_badge($adminCounts['unread_messages']), 'href' => $base . 'admin/messages.php'],
    ['label' => 'Warranty' . nav_badge($adminCounts['pending_warranty']), 'href' => $base . 'admin/warranty-claims.php'],
    ['label' => 'Reviews', 'href' => $base . 'admin/reviews.php'],
    ['label' => 'Vouchers', 'href' => $base . 'admin/vouchers.php'],
    ['label' => 'Discounts', 'href' => $base . 'admin/discounts.php'],
    ['label' => 'Customers', 'href' => $base . 'admin/customers.php'],
    ['label' => 'Logout', 'href' => $base . 'logout.php'],
  ];
} elseif ($role === 'customer') {
  $mobileLinks = [
    ['label' => 'Home', 'href' => $base . 'home.php'],
    ['label' => 'Products', 'href' => $base . 'products.php'],
    ['label' => 'Profile' . nav_badge($customerCounts['active_vouchers'] + $customerCounts['pending_warranty']), 'href' => $base . 'profile.php'],
    ['label' => 'Messages' . nav_badge($customerCounts['unread_messages']), 'href' => $base . 'customer_messages.php'],
    ['label' => 'Wishlist' . nav_badge($wishlistTotal), 'href' => $base . 'wishlist.php'],
    ['label' => 'Cart ' . nav_badge($count), 'href' => $base . 'cart.php'],
    ['label' => 'Logout', 'href' => $base . 'logout.php'],
  ];
} else {
  $mobileLinks = [
    ['label' => 'Home', 'href' => $base . ($isLanding ? 'index.php' : 'home.php')],
    ['label' => 'Products', 'href' => $base . 'products.php'],
    ['label' => 'Login', 'href' => $base . 'login.php'],
  ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title) ?></title>
  <link rel="stylesheet" href="<?= $base ?>css/style.css?v=20260707-responsive-system">
  <link rel="stylesheet" href="<?= $base ?>css/chatbot.css?v=20260707-responsive-system">
</head>
<body>
  <header class="nav <?= $isLanding ? 'landing-nav' : '' ?> <?= $role === 'admin' ? 'admin-top-nav' : '' ?>">
    <div class="container nav-inner">
      <a class="brand" href="<?= e($homeHref) ?>"><span class="brand-mark">N</span><span>NovaTech Gadgets</span></a>
      <button class="mobile-menu-toggle" type="button" aria-label="Open navigation menu" aria-controls="mobileNavMenu" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <?php if ($isLanding): ?>
        <nav class="nav-links landing-only-nav">
          <a class="icon-link" href="<?= $base ?>login.php">Login</a>
        </nav>
      <?php elseif ($role === 'admin'): ?>
        <nav class="nav-links admin-nav-links" aria-label="Admin navigation">
          <a class="<?= $adminActive('dashboard.php') ?>" href="<?= $base ?>admin/dashboard.php">Dashboard</a>
          <a class="<?= $adminActive('products.php') ?>" href="<?= $base ?>admin/products.php">Products</a>
          <a class="<?= $adminActive('orders.php') ?>" href="<?= $base ?>admin/orders.php">Orders</a>
          <a class="<?= $adminActive('product-requests.php') ?>" href="<?= $base ?>admin/product-requests.php">Requests<?= nav_badge($adminCounts['pending_product_requests']) ?></a>
          <a class="<?= $adminActive('messages.php') ?>" href="<?= $base ?>admin/messages.php">Messages<?= nav_badge($adminCounts['unread_messages']) ?></a>
          <details class="admin-more-menu">
            <summary class="<?= $adminMoreActive ?>">More</summary>
            <div class="admin-more-panel">
              <a class="<?= $adminActive('warranty-claims.php') ?>" href="<?= $base ?>admin/warranty-claims.php">Warranty<?= nav_badge($adminCounts['pending_warranty']) ?></a>
              <a class="<?= $adminActive('vouchers.php') ?>" href="<?= $base ?>admin/vouchers.php">Vouchers</a>
              <a class="<?= $adminActive('discounts.php') ?>" href="<?= $base ?>admin/discounts.php">Discounts</a>
              <a class="<?= $adminActive('reviews.php') ?>" href="<?= $base ?>admin/reviews.php">Reviews</a>
              <a class="<?= $adminActive('customers.php') ?>" href="<?= $base ?>admin/customers.php">Customers</a>
            </div>
          </details>
        </nav>
        <div class="nav-actions admin-nav-actions"><span class="icon-link role-label admin-role-label">Admin: <?= e($adminName) ?></span><a class="icon-link admin-logout-link" href="<?= $base ?>logout.php">Logout</a></div>
      <?php elseif ($role === 'customer'): ?>
        <nav class="nav-links">
          <a class="<?= $active === 'home' ? 'active' : '' ?>" href="<?= $base ?>home.php">Home</a>
          <a class="<?= $active === 'products' ? 'active' : '' ?>" href="<?= $base ?>products.php">Products</a>
          <a class="<?= $active === 'profile' ? 'active' : '' ?>" href="<?= $base ?>profile.php">Profile<?= nav_badge($customerCounts['active_vouchers'] + $customerCounts['pending_warranty']) ?></a>
          <a class="<?= $active === 'messages' ? 'active' : '' ?>" href="<?= $base ?>customer_messages.php">Messages<?= nav_badge($customerCounts['unread_messages']) ?></a>
          <a class="<?= $active === 'wishlist' ? 'active' : '' ?>" href="<?= $base ?>wishlist.php">Wishlist<?= nav_badge($wishlistTotal) ?></a>
        </nav>
        <div class="nav-actions"><span class="icon-link role-label"><?= e($user['full_name']) ?></span><a class="icon-link" href="<?= $base ?>logout.php">Logout</a><a class="icon-link" href="<?= $base ?>cart.php">Cart <span class="cart-count"><?= $count ?></span></a></div>
      <?php elseif ($role === 'guest'): ?>
        <nav class="nav-links">
          <a class="<?= $active === 'home' ? 'active' : '' ?>" href="<?= $base ?>home.php">Home</a>
          <a class="<?= $active === 'products' ? 'active' : '' ?>" href="<?= $base ?>products.php">Products</a>
        </nav>
        <div class="nav-actions"><span class="icon-link role-label">Guest</span><a class="icon-link" href="<?= $base ?>login.php">Login</a></div>
      <?php else: ?>
        <nav class="nav-links">
          <a class="<?= $active === 'home' ? 'active' : '' ?>" href="<?= $base ?>home.php">Home</a>
          <a class="<?= $active === 'products' ? 'active' : '' ?>" href="<?= $base ?>products.php">Products</a>
        </nav>
        <div class="nav-actions"><a class="icon-link" href="<?= $base ?>login.php">Login</a></div>
      <?php endif; ?>
      <nav class="mobile-nav-menu" id="mobileNavMenu" aria-label="Mobile navigation">
        <?php if ($role === 'admin'): ?>
          <span class="mobile-admin-label">Admin: <?= e($adminName) ?></span>
        <?php endif; ?>
        <?php foreach ($mobileLinks as $link): ?>
          <a href="<?= e($link['href']) ?>"><?= $link['label'] ?></a>
        <?php endforeach; ?>
      </nav>
    </div>
  </header>
