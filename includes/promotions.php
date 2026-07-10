<?php
declare(strict_types=1);

require_once __DIR__ . '/messages.php';

function active_discounts(PDO $pdo): array
{
    ensure_customer_engagement_schema($pdo);
    $stmt = $pdo->query("SELECT * FROM discounts WHERE status = 'active' AND (start_date IS NULL OR start_date <= NOW()) AND (end_date IS NULL OR end_date >= NOW()) ORDER BY FIELD(applies_to, 'product', 'category', 'all'), id DESC");
    return $stmt->fetchAll();
}

function best_discount_for_product(PDO $pdo, array $product): ?array
{
    $best = null;
    $price = (float) $product['price'];
    foreach (active_discounts($pdo) as $discount) {
        $applies = false;
        if ($discount['applies_to'] === 'all') {
            $applies = true;
        } elseif ($discount['applies_to'] === 'category' && strcasecmp((string) $discount['category'], (string) ($product['category_name'] ?? '')) === 0) {
            $applies = true;
        } elseif ($discount['applies_to'] === 'product' && (int) $discount['product_id'] === (int) $product['product_id']) {
            $applies = true;
        }

        if (!$applies) {
            continue;
        }

        $amount = $discount['discount_type'] === 'percentage'
            ? $price * ((float) $discount['discount_value'] / 100)
            : (float) $discount['discount_value'];
        $amount = min($price, max(0.0, $amount));
        if (!$best || $amount > (float) $best['discount_amount']) {
            $best = $discount;
            $best['discount_amount'] = $amount;
            $best['discounted_price'] = max(0.0, $price - $amount);
        }
    }

    return $best;
}

function discounted_product_price(PDO $pdo, array $product, float $basePrice): array
{
    $discountProduct = $product;
    $discountProduct['price'] = $basePrice;
    $discount = best_discount_for_product($pdo, $discountProduct);
    if (!$discount) {
        return ['price' => $basePrice, 'original' => $basePrice, 'discount' => null, 'save' => 0.0];
    }

    $save = min($basePrice, (float) $discount['discount_amount']);
    return ['price' => max(0.0, $basePrice - $save), 'original' => $basePrice, 'discount' => $discount, 'save' => $save];
}

function validate_customer_voucher(PDO $pdo, int $customerId, string $code, float $subtotal): array
{
    ensure_customer_engagement_schema($pdo);
    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = ? AND customer_id = ? LIMIT 1");
    $stmt->execute([strtoupper(trim($code)), $customerId]);
    $voucher = $stmt->fetch();
    if (!$voucher || $voucher['status'] !== 'active' || strtotime((string) $voucher['expires_at']) < time() || (float) $voucher['min_order_amount'] > $subtotal) {
        return ['ok' => false, 'voucher' => null, 'amount' => 0.0, 'error' => 'Invalid or expired voucher code.'];
    }

    $amount = $voucher['discount_type'] === 'fixed'
        ? (float) $voucher['discount_value']
        : $subtotal * ((float) $voucher['discount_value'] / 100);

    return ['ok' => true, 'voucher' => $voucher, 'amount' => min($subtotal, max(0.0, $amount)), 'error' => ''];
}

function create_welcome_voucher(PDO $pdo, int $customerId, int $adminId): array
{
    ensure_customer_engagement_schema($pdo);
    $existing = $pdo->prepare("SELECT * FROM vouchers WHERE customer_id = ? AND voucher_type = 'welcome_profile_completion' LIMIT 1");
    $existing->execute([$customerId]);
    $voucher = $existing->fetch();
    if ($voucher) {
        return ['created' => false, 'voucher' => $voucher];
    }

    do {
        $code = 'WELCOME15-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $check = $pdo->prepare('SELECT id FROM vouchers WHERE code = ?');
        $check->execute([$code]);
    } while ($check->fetch());

    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
    $stmt = $pdo->prepare("INSERT INTO vouchers (code, customer_id, voucher_type, discount_type, discount_value, min_order_amount, status, created_by_admin_id, expires_at) VALUES (?, ?, 'welcome_profile_completion', 'fixed', 15.00, 0.00, 'active', ?, ?)");
    $stmt->execute([$code, $customerId, $adminId, $expiresAt]);
    $voucherId = (int) $pdo->lastInsertId();
    $voucher = $pdo->prepare('SELECT * FROM vouchers WHERE id = ?');
    $voucher->execute([$voucherId]);
    return ['created' => true, 'voucher' => $voucher->fetch()];
}
