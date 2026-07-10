<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/wishlist.php';

if (!current_user() || current_role() !== 'customer') {
    redirect('login.php');
}

$customerId = (int) $_SESSION['user']['user_id'];
$productId = (int) ($_POST['product_id'] ?? 0);
$action = $_POST['action'] ?? 'add';
$returnTo = (string) ($_POST['return_to'] ?? 'products.php');

if (!preg_match('/^[A-Za-z0-9_\/.\-?=&%]+$/', $returnTo)) {
    $returnTo = 'products.php';
}

$productStmt = $pdo->prepare("SELECT product_id FROM products WHERE product_id = ? AND status = 'active'");
$productStmt->execute([$productId]);

if ($productStmt->fetch()) {
    if ($action === 'remove') {
        remove_from_wishlist($pdo, $customerId, $productId);
    } else {
        add_to_wishlist($pdo, $customerId, $productId);
    }
}

redirect($returnTo);
