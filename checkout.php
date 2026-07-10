<?php
$page_title = 'Checkout | NovaTech Gadgets';
$active = 'products';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mail.php';
require_once __DIR__ . '/includes/promotions.php';
require_login();

function checkout_banks(): array
{
    return ['Maybank', 'CIMB Bank', 'Public Bank', 'RHB Bank', 'Hong Leong Bank', 'AmBank', 'Bank Islam', 'Affin Bank', 'OCBC Malaysia', 'UOB Malaysia'];
}

function checkout_online_banks(): array
{
    return ['Maybank2u', 'CIMB Clicks', 'Public Bank PBe', 'RHB Now', 'Hong Leong Connect', 'AmOnline', 'Bank Islam Internet Banking', 'AffinOnline', 'OCBC Online Banking', 'UOB Personal Internet Banking', "Touch 'n Go eWallet", 'Boost eWallet', 'GrabPay'];
}

function checkout_delivery_options(): array
{
    return [
        'standard' => ['label' => 'Standard Delivery', 'fee' => 8.00, 'estimate' => '3-5 working days', 'pickup_location' => ''],
        'express' => ['label' => 'Express Delivery', 'fee' => 15.00, 'estimate' => '1-2 working days', 'pickup_location' => ''],
        'pickup' => ['label' => 'Store Pickup', 'fee' => 0.00, 'estimate' => 'Ready for pickup within 24 hours', 'pickup_location' => 'NovaTech Gadgets Store, Kuala Lumpur'],
    ];
}

function checkout_delivery_option(string $method): array
{
    $options = checkout_delivery_options();
    return $options[$method] ?? $options['standard'];
}

function ensure_checkout_schema(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }

    $queries = [
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS invoice_no VARCHAR(40) NULL AFTER order_id",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS customer_email VARCHAR(160) NULL AFTER shipping_name",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS bank_name VARCHAR(80) NULL AFTER payment_method",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS card_last4 VARCHAR(4) NULL AFTER bank_name",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) NULL AFTER card_last4",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS tax DECIMAL(10,2) NULL AFTER subtotal",
        "ALTER TABLE orders MODIFY order_status ENUM('Paid','Processing','Packed','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Paid'",
        "ALTER TABLE payments ADD COLUMN IF NOT EXISTS bank_name VARCHAR(80) NULL AFTER payment_method",
        "ALTER TABLE payments ADD COLUMN IF NOT EXISTS card_last4 VARCHAR(4) NULL AFTER bank_name",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS warranty_period VARCHAR(80) NOT NULL DEFAULT '1 Year'",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS warranty_type VARCHAR(120) NOT NULL DEFAULT 'Manufacturer Warranty'",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS warranty_description TEXT NULL",
        "ALTER TABLE order_items ADD COLUMN IF NOT EXISTS product_category VARCHAR(100) NULL AFTER product_id",
        "ALTER TABLE order_items ADD COLUMN IF NOT EXISTS product_name VARCHAR(180) NULL AFTER product_category",
        "ALTER TABLE order_items ADD COLUMN IF NOT EXISTS warranty_period VARCHAR(80) NULL AFTER selected_options",
        "ALTER TABLE order_items ADD COLUMN IF NOT EXISTS warranty_type VARCHAR(120) NULL AFTER warranty_period",
        "ALTER TABLE order_items ADD COLUMN IF NOT EXISTS warranty_description TEXT NULL AFTER warranty_type",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS voucher_code VARCHAR(40) NULL",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_method VARCHAR(50) NULL",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_estimate VARCHAR(100) NULL",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS pickup_location TEXT NULL",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS account_holder_name VARCHAR(150) NULL",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100) NULL",
        "ALTER TABLE payments ADD COLUMN IF NOT EXISTS account_holder_name VARCHAR(150) NULL",
        "ALTER TABLE payments ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100) NULL",
    ];

    foreach ($queries as $query) {
        try {
            $pdo->exec($query);
        } catch (Throwable $exception) {
            // The migration SQL file contains the same changes for manual import if an older MySQL version rejects IF NOT EXISTS.
        }
    }
    $done = true;
}

function checkout_cart_items(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT c.quantity, c.selected_color, c.selected_spec, c.unit_price, p.*, cat.category_name FROM cart c JOIN products p ON p.product_id = c.product_id LEFT JOIN categories cat ON cat.category_id = p.category_id WHERE c.user_id = ?');
    $stmt->execute([$userId]);
    return array_map(function (array $item) use ($pdo): array {
        $variantPrice = product_variant_price($item['product_name'], (string) ($item['selected_spec'] ?? ''), (float) $item['price']);
        $item['unit_price'] = discounted_product_price($pdo, $item, $variantPrice)['price'];
        return $item;
    }, $stmt->fetchAll());
}

function checkout_totals(array $items, string $deliveryMethod = 'standard'): array
{
    $subtotal = array_reduce($items, fn($sum, $item) => $sum + ((float) ($item['unit_price'] ?? $item['price']) * (int) $item['quantity']), 0.0);
    $delivery = $subtotal > 0 ? checkout_delivery_option($deliveryMethod) : checkout_delivery_option('pickup');
    $shipping = $subtotal > 0 ? (float) $delivery['fee'] : 0.00;
    $tax = $subtotal * 0.06;
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'delivery_method' => $deliveryMethod,
        'delivery_label' => $delivery['label'],
        'delivery_estimate' => $delivery['estimate'],
        'pickup_location' => $delivery['pickup_location'],
        'tax' => $tax,
        'discount' => 0.0,
        'voucher_code' => '',
        'voucher_id' => null,
        'total' => $subtotal + $shipping + $tax,
    ];
}

function checkout_totals_with_voucher(array $totals, array $voucherResult): array
{
    if (!$voucherResult['ok']) {
        return $totals;
    }

    $discount = min((float) $totals['subtotal'], (float) $voucherResult['amount']);
    $afterDiscountSubtotal = max(0.0, (float) $totals['subtotal'] - $discount);
    $totals['discount'] = $discount;
    $totals['voucher_code'] = $voucherResult['voucher']['code'];
    $totals['voucher_id'] = (int) $voucherResult['voucher']['id'];
    $totals['tax'] = $afterDiscountSubtotal * 0.06;
    $totals['total'] = $afterDiscountSubtotal + (float) $totals['shipping'] + (float) $totals['tax'];
    return $totals;
}

function normalize_malaysia_phone(string $countryCode, string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone);
    if (str_starts_with($digits, '60')) {
        $digits = substr($digits, 2);
    }
    if (str_starts_with($digits, '0')) {
        $digits = substr($digits, 1);
    }

    if ($countryCode !== '+60' || !preg_match('/^1\d{8,9}$/', $digits)) {
        return '';
    }

    return '+60' . $digits;
}

function valid_expiry(string $expiry): bool
{
    if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry, $matches)) {
        return false;
    }

    $month = (int) $matches[1];
    $year = 2000 + (int) $matches[2];
    $expiryEnd = strtotime(sprintf('%04d-%02d-01 +1 month -1 day 23:59:59', $year, $month));
    return $expiryEnd !== false && $expiryEnd >= time();
}

function checkout_form_data(array $source, array $currentUser): array
{
    $countryCode = $source['country_code'] ?? '+60';
    return [
        'name' => trim((string) ($source['shipping_name'] ?? $currentUser['full_name'] ?? '')),
        'email' => trim((string) ($source['customer_email'] ?? $currentUser['email'] ?? '')),
        'country_code' => $countryCode === '+60' ? '+60' : '+60',
        'phone_input' => trim((string) ($source['shipping_phone'] ?? '')),
        'phone' => normalize_malaysia_phone((string) $countryCode, (string) ($source['shipping_phone'] ?? '')),
        'address' => trim((string) ($source['shipping_address'] ?? '')),
        'delivery_method' => array_key_exists((string) ($source['delivery_method'] ?? 'standard'), checkout_delivery_options()) ? (string) ($source['delivery_method'] ?? 'standard') : 'standard',
        'payment' => trim((string) ($source['payment_method'] ?? 'Credit / Debit Card')),
        'bank' => trim((string) ($source['bank_name'] ?? '')),
        'online_bank' => trim((string) ($source['online_bank_name'] ?? '')),
        'cardholder' => trim((string) ($source['cardholder_name'] ?? '')),
        'account_holder' => trim((string) ($source['account_holder_name'] ?? '')),
        'payment_reference' => preg_replace('/[^A-Za-z0-9\-_. ]+/', '', trim((string) ($source['payment_reference'] ?? ''))),
        'voucher_code' => strtoupper(trim((string) ($source['voucher_code'] ?? ''))),
        'card_last4' => '',
    ];
}

function validate_checkout(array &$data, array $source, array $items): array
{
    $errors = [];
    $banks = checkout_banks();
    $email = $data['email'];
    $cardNumber = preg_replace('/\D+/', '', (string) ($source['card_number'] ?? ''));
    $cvv = preg_replace('/\D+/', '', (string) ($source['cvv'] ?? ''));
    $expiry = trim((string) ($source['expiry_date'] ?? ''));

    if (!$items) {
        $errors[] = 'Your cart is empty.';
    }
    if ($data['name'] === '') {
        $errors[] = 'Shipping name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address, for example user@gmail.com.';
    }
    if ($data['phone'] === '') {
        $errors[] = 'Please enter a valid Malaysia phone number after +60, for example 123456789.';
    }
    if ($data['address'] === '') {
        $errors[] = 'Shipping address is required.';
    }
    if (!in_array($data['payment'], ['Credit / Debit Card', 'Online Banking', 'Cash on Delivery'], true)) {
        $errors[] = 'Please select a valid payment method.';
    }
    if (!array_key_exists($data['delivery_method'], checkout_delivery_options())) {
        $errors[] = 'Please select a valid delivery method.';
    }

    if ($data['payment'] === 'Credit / Debit Card') {
        if (!in_array($data['bank'], $banks, true)) {
            $errors[] = 'Please select a Malaysian bank.';
        }
        if ($data['cardholder'] === '') {
            $errors[] = 'Cardholder name is required.';
        }
        if (!preg_match('/^\d{16}$/', $cardNumber)) {
            $errors[] = 'Card number must be numeric and 16 digits.';
        } else {
            $data['card_last4'] = substr($cardNumber, -4);
        }
        if (!valid_expiry($expiry)) {
            $errors[] = 'Expiry date must be valid and in MM/YY format.';
        }
        if (!preg_match('/^\d{3}$/', $cvv)) {
            $errors[] = 'CVV must be numeric and 3 digits.';
        }
    } elseif ($data['payment'] === 'Online Banking') {
        $onlineBanks = checkout_online_banks();
        if (!in_array($data['online_bank'], $onlineBanks, true)) {
            $errors[] = 'Please select an online banking bank.';
        }
        if ($data['account_holder'] === '') {
            $errors[] = 'Account holder name is required for Online Banking.';
        }
        $data['bank'] = $data['online_bank'];
    } elseif ($data['payment'] === 'Cash on Delivery') {
        $data['bank'] = '';
        $data['cardholder'] = '';
        $data['card_last4'] = '';
        $data['account_holder'] = '';
        $data['payment_reference'] = '';
    }

    return $errors;
}

function snapshot_items(array $items): array
{
    return array_map(function (array $item): array {
        $unitPrice = (float) ($item['unit_price'] ?? $item['price']);
        $quantity = (int) $item['quantity'];
        $selectedOptions = trim(($item['selected_spec'] ?? '') . ' ' . ($item['selected_color'] ?? ''));
        return [
            'product_id' => (int) $item['product_id'],
            'product_name' => (string) $item['product_name'],
            'product_category' => (string) ($item['category_name'] ?? 'Uncategorized'),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $unitPrice * $quantity,
            'selected_options' => $selectedOptions,
            'warranty_period' => (string) ($item['warranty_period'] ?? '1 Year'),
            'warranty_type' => (string) ($item['warranty_type'] ?? 'Manufacturer Warranty'),
            'warranty_description' => (string) ($item['warranty_description'] ?? 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'),
        ];
    }, $items);
}

function generate_checkout_otp(array $data, array $items, array $totals): array
{
    $otp = (string) random_int(100000, 999999);
    $_SESSION['checkout_otp'] = [
        'code' => $otp,
        'expires_at' => time() + 300,
    ];
    $_SESSION['pending_checkout'] = [
        'data' => $data,
        'items' => snapshot_items($items),
        'totals' => $totals,
    ];

    return novatech_send_otp_email($data['email'], $data['name'], $otp);
}

function place_verified_order(PDO $pdo, int $userId, array $pending): array
{
    ensure_checkout_schema($pdo);
    $data = $pending['data'];
    $items = $pending['items'];
    $totals = $pending['totals'];
    $invoiceNo = 'NVG-' . date('Ymd-His') . '-' . random_int(100, 999);
    $delivery = checkout_delivery_option((string) ($data['delivery_method'] ?? 'standard'));
    $paymentStatus = $data['payment'] === 'Cash on Delivery' ? 'Pending' : 'Paid';
    $orderStatus = $data['payment'] === 'Cash on Delivery' ? 'Processing' : 'Paid';

    $pdo->beginTransaction();
    $order = $pdo->prepare("INSERT INTO orders (invoice_no, user_id, total_amount, order_status, shipping_name, customer_email, shipping_phone, shipping_address, payment_method, bank_name, card_last4, subtotal, tax, voucher_code, discount_amount, delivery_method, delivery_fee, delivery_estimate, pickup_location, account_holder_name, payment_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $order->execute([
        $invoiceNo,
        $userId,
        $totals['total'],
        $orderStatus,
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['address'],
        $data['payment'],
        $data['bank'] ?: null,
        $data['card_last4'] ?: null,
        $totals['subtotal'],
        $totals['tax'],
        $totals['voucher_code'] ?: null,
        $totals['discount'] ?? 0.0,
        $data['delivery_method'],
        $totals['shipping'],
        $delivery['estimate'],
        $delivery['pickup_location'] ?: null,
        $data['account_holder'] ?: null,
        $data['payment_reference'] ?: null,
    ]);
    $orderId = (int) $pdo->lastInsertId();

    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_category, product_name, quantity, price, subtotal, selected_options, warranty_period, warranty_type, warranty_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stockStmt = $pdo->prepare('UPDATE products SET stock_quantity = GREATEST(stock_quantity - ?, 0) WHERE product_id = ?');
    foreach ($items as $item) {
        $itemStmt->execute([$orderId, $item['product_id'], $item['product_category'], $item['product_name'], $item['quantity'], $item['unit_price'], $item['subtotal'], $item['selected_options'], $item['warranty_period'], $item['warranty_type'], $item['warranty_description']]);
        $stockStmt->execute([$item['quantity'], $item['product_id']]);
    }

    $paymentStmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, bank_name, card_last4, account_holder_name, payment_reference, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $paymentStmt->execute([$orderId, $data['payment'], $data['bank'] ?: null, $data['card_last4'] ?: null, $data['account_holder'] ?: null, $data['payment_reference'] ?: null, $paymentStatus]);
    $clear = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
    $clear->execute([$userId]);
    if (!empty($totals['voucher_id'])) {
        $usedVoucher = $pdo->prepare("UPDATE vouchers SET status = 'used', used_at = NOW() WHERE id = ? AND customer_id = ?");
        $usedVoucher->execute([(int) $totals['voucher_id'], $userId]);
    }
    $pdo->commit();

    $invoiceData = [
        'invoice_no' => $invoiceNo,
        'customer_name' => $data['name'],
        'customer_email' => $data['email'],
        'customer_phone' => $data['phone'],
        'shipping_address' => $data['address'],
        'payment_method' => $data['payment'],
        'bank_name' => $data['bank'],
        'card_last4' => $data['card_last4'],
        'account_holder_name' => $data['account_holder'],
        'payment_reference' => $data['payment_reference'],
        'delivery_method' => $delivery['label'],
        'delivery_fee_display' => money($totals['shipping']),
        'delivery_estimate' => $delivery['estimate'],
        'pickup_location' => $delivery['pickup_location'],
        'created_at' => date('Y-m-d H:i:s'),
        'subtotal_display' => money($totals['subtotal']),
        'tax_display' => money($totals['tax']),
        'voucher_code' => $totals['voucher_code'] ?? '',
        'discount_display' => money($totals['discount'] ?? 0),
        'total_display' => money($totals['total']),
        'payment_status' => $paymentStatus === 'Pending' ? 'Pending Payment' : 'Paid',
    ];
    $invoiceResult = novatech_send_invoice_email($data['email'], $invoiceData, $items);

    return ['order_id' => $orderId, 'invoice_no' => $invoiceNo, 'invoice_result' => $invoiceResult];
}

ensure_checkout_schema($pdo);
ensure_customer_engagement_schema($pdo);
$userId = (int) $_SESSION['user']['user_id'];
$items = checkout_cart_items($pdo, $userId);
$currentUser = current_user();
$pending = $_SESSION['pending_checkout'] ?? null;
$prefillSource = $pending['data'] ?? [];
$form = checkout_form_data($prefillSource, $currentUser);
$totals = checkout_totals($items, $form['delivery_method']);
if (!empty($pending['totals']) && is_array($pending['totals'])) {
    $totals = $pending['totals'];
}
$errors = [];
$notice = '';
$otpReady = is_array($pending);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['checkout_action'] ?? 'send_otp';

    if ($action === 'send_otp') {
        $form = checkout_form_data($_POST, $currentUser);
        $totals = checkout_totals($items, $form['delivery_method']);
        $errors = validate_checkout($form, $_POST, $items);

        if (!$errors) {
            if ($form['voucher_code'] !== '') {
                $voucherResult = validate_customer_voucher($pdo, $userId, $form['voucher_code'], $totals['subtotal']);
                if (!$voucherResult['ok']) {
                    $errors[] = $voucherResult['error'];
                } else {
                    $totals = checkout_totals_with_voucher($totals, $voucherResult);
                }
            }
        }

        if (!$errors) {
            $result = generate_checkout_otp($form, $items, $totals);
            $notice = 'OTP has been sent to your email. Please enter it below to complete payment.';
            if (!$result['sent']) {
                $notice .= ' Demo mode: email was saved to storage/mail_outbox.';
            }
            $pending = $_SESSION['pending_checkout'] ?? null;
            $otpReady = true;
        }
    }

    if ($action === 'resend_otp') {
        if (!is_array($pending)) {
            $errors[] = 'Please submit your checkout details first.';
        } else {
            $totals = is_array($pending['totals'] ?? null) ? $pending['totals'] : checkout_totals($items, (string) ($pending['data']['delivery_method'] ?? 'standard'));
            $result = generate_checkout_otp($pending['data'], $items, $totals);
            $notice = 'A new OTP has been sent. It expires in 5 minutes.';
            if (!$result['sent']) {
                $notice .= ' Demo mode: email was saved to storage/mail_outbox.';
            }
            $otpReady = true;
        }
    }

    if ($action === 'verify_otp') {
        $enteredOtp = trim((string) ($_POST['otp_code'] ?? ''));
        $otp = $_SESSION['checkout_otp'] ?? null;
        $pending = $_SESSION['pending_checkout'] ?? null;

        if (!is_array($pending) || !is_array($otp)) {
            $errors[] = 'OTP session expired. Please submit checkout details again.';
        } elseif (time() > (int) $otp['expires_at']) {
            $errors[] = 'OTP expired. Please resend OTP.';
            $otpReady = true;
        } elseif (!hash_equals((string) $otp['code'], $enteredOtp)) {
            $errors[] = 'Incorrect OTP. Please check your email and try again.';
            $otpReady = true;
        } else {
            try {
                $orderResult = place_verified_order($pdo, $userId, $pending);
                unset($_SESSION['checkout_otp'], $_SESSION['pending_checkout']);
                redirect('profile.php?ordered=paid&invoice=' . urlencode($orderResult['invoice_no']));
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Payment verification passed, but order saving failed. Please try again.';
                $otpReady = true;
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Checkout</h1><p>Complete your shipping and payment details.</p></div>
    <div class="checkout-layout">
      <form class="panel checkout-form" method="post" data-checkout-form novalidate>
        <input type="hidden" name="checkout_action" value="send_otp">
        <h2>Customer information</h2>
        <?php foreach ($errors as $error): ?><div class="notice error"><?= e($error) ?></div><?php endforeach; ?>
        <?php if ($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
        <div class="form-grid">
          <div class="field"><label>Shipping name</label><input name="shipping_name" required value="<?= e($form['name']) ?>"></div>
          <div class="field"><label>Email address</label><input type="email" name="customer_email" required placeholder="user@gmail.com" value="<?= e($form['email']) ?>" data-email-input><small class="field-error" data-error-for="customer_email"></small></div>
          <div class="field full">
            <label>Phone</label>
            <div class="phone-input">
              <input type="hidden" name="country_code" value="+60">
              <span class="phone-prefix">Malaysia +60</span>
              <input name="shipping_phone" inputmode="numeric" pattern="[0-9 ]+" required placeholder="123456789" value="<?= e($form['phone_input']) ?>" data-phone-input>
            </div>
            <small class="field-error" data-error-for="shipping_phone"></small>
          </div>
          <div class="field full"><label>Shipping address</label><textarea name="shipping_address" required><?= e($form['address']) ?></textarea></div>
          <div class="field full"><label>Voucher Code</label><input name="voucher_code" value="<?= e($form['voucher_code']) ?>" placeholder="WELCOME15-XXXXXX"></div>
          <div class="field full">
            <label>Delivery option</label>
            <div class="delivery-options" data-delivery-options>
              <?php foreach (checkout_delivery_options() as $method => $delivery): ?>
                <label class="delivery-option<?= $form['delivery_method'] === $method ? ' selected' : '' ?>">
                  <input type="radio" name="delivery_method" value="<?= e($method) ?>"<?= $form['delivery_method'] === $method ? ' checked' : '' ?> data-delivery-fee="<?= e(number_format((float) $delivery['fee'], 2, '.', '')) ?>" data-delivery-label="<?= e($delivery['label']) ?>" data-delivery-estimate="<?= e($delivery['estimate']) ?>" data-pickup-location="<?= e($delivery['pickup_location']) ?>">
                  <span>
                    <strong><?= e($delivery['label']) ?></strong>
                    <small><?= e($delivery['estimate']) ?> · <?= money((float) $delivery['fee']) ?></small>
                    <?php if ($delivery['pickup_location'] !== ''): ?><small><?= e($delivery['pickup_location']) ?></small><?php endif; ?>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="field full"><label>Payment method</label><select name="payment_method" required data-payment-method><option<?= $form['payment'] === 'Credit / Debit Card' ? ' selected' : '' ?>>Credit / Debit Card</option><option<?= $form['payment'] === 'Online Banking' ? ' selected' : '' ?>>Online Banking</option><option<?= $form['payment'] === 'Cash on Delivery' ? ' selected' : '' ?>>Cash on Delivery</option></select></div>
        </div>

        <div class="payment-card-fields payment-detail-card" data-card-fields>
          <h2>Card payment</h2>
          <p class="form-hint">Demo only. Full card number and CVV are validated but not saved.</p>
          <div class="form-grid">
            <div class="field full"><label>Malaysian bank</label><select name="bank_name" data-card-required><option value="">Select bank</option><?php foreach (checkout_banks() as $bank): ?><option value="<?= e($bank) ?>"<?= $form['bank'] === $bank ? ' selected' : '' ?>><?= e($bank) ?></option><?php endforeach; ?></select><small class="field-error" data-error-for="bank_name"></small></div>
            <div class="field"><label>Cardholder name</label><input name="cardholder_name" data-card-required value="<?= e($form['cardholder']) ?>"><small class="field-error" data-error-for="cardholder_name"></small></div>
            <div class="field"><label>Card number</label><input name="card_number" inputmode="numeric" maxlength="19" autocomplete="off" placeholder="1234 5678 9012 3456" data-card-number data-card-required><small class="field-error" data-error-for="card_number"></small></div>
            <div class="field"><label>Expiry date</label><input name="expiry_date" placeholder="MM/YY" maxlength="5" data-expiry data-card-required><small class="field-error" data-error-for="expiry_date"></small></div>
            <div class="field"><label>CVV</label><input name="cvv" inputmode="numeric" maxlength="3" autocomplete="off" data-cvv data-card-required><small class="field-error" data-error-for="cvv"></small></div>
          </div>
        </div>

        <div class="payment-card-fields payment-detail-card" data-online-banking-fields hidden>
          <h2>Online Banking</h2>
          <p class="form-hint">Choose your Malaysian online banking provider. Card details are not required for Online Banking.</p>
          <div class="form-grid">
            <div class="field full"><label>Online banking bank</label><select name="online_bank_name" data-online-required><option value="">Select online bank</option><?php foreach (checkout_online_banks() as $bank): ?><option value="<?= e($bank) ?>"<?= $form['online_bank'] === $bank ? ' selected' : '' ?>><?= e($bank) ?></option><?php endforeach; ?></select><small class="field-error" data-error-for="online_bank_name"></small></div>
            <div class="field full"><label>Account holder name</label><input name="account_holder_name" value="<?= e($form['account_holder']) ?>" data-online-required placeholder="Name shown on your bank account"><small class="field-error" data-error-for="account_holder_name"></small></div>
            <div class="field full"><label>Payment reference <span class="muted">(optional)</span></label><input name="payment_reference" value="<?= e($form['payment_reference']) ?>" placeholder="FPX / transfer reference number"></div>
          </div>
        </div>

        <div class="payment-card-fields payment-detail-card cod-note" data-cod-fields hidden>
          <h2>Cash on Delivery</h2>
          <p class="form-hint">No bank or card details are needed. Payment status will be marked as pending until delivery payment is collected.</p>
        </div>

        <button class="btn" type="submit">Place Order</button>
      </form>

      <aside class="panel checkout-summary" data-checkout-summary data-subtotal="<?= e(number_format((float) $totals['subtotal'], 2, '.', '')) ?>" data-discount="<?= e(number_format((float) ($totals['discount'] ?? 0), 2, '.', '')) ?>">
        <h2>Order summary</h2>
        <?php foreach ($items as $item): ?>
          <?php $selectedOptions = trim(($item['selected_spec'] ?? '') . ' ' . ($item['selected_color'] ?? '')); ?>
          <div class="summary-row"><span><?= e($item['product_name']) ?><?php if ($selectedOptions !== ''): ?> <small><?= e($selectedOptions) ?></small><?php endif; ?> x <?= (int) $item['quantity'] ?></span><strong><?= money((float) ($item['unit_price'] ?? $item['price']) * (int) $item['quantity']) ?></strong></div>
        <?php endforeach; ?>
        <div class="summary-row"><span>Subtotal</span><strong><?= money($totals['subtotal']) ?></strong></div>
        <?php if (!empty($totals['discount'])): ?><div class="summary-row"><span>Voucher Discount <?= e($totals['voucher_code']) ?></span><strong>-<?= money($totals['discount']) ?></strong></div><?php endif; ?>
        <div class="summary-row"><span>Delivery fee <small data-delivery-summary-label><?= e($totals['delivery_label'] ?? 'Standard Delivery') ?></small></span><strong data-summary-delivery><?= money($totals['shipping']) ?></strong></div>
        <div class="summary-row"><span>Estimated tax</span><strong data-summary-tax><?= money($totals['tax']) ?></strong></div>
        <div class="summary-row total"><span>Total</span><span data-summary-total><?= money($totals['total']) ?></span></div>
      </aside>
    </div>

    <?php if ($otpReady): ?>
      <section class="panel checkout-otp" id="otp-section">
        <div>
          <span class="badge paid">OTP verification</span>
          <h2>Enter the 6-digit OTP</h2>
          <p>We sent the OTP to <?= e(($pending['data']['email'] ?? $form['email'])) ?>. It expires after 5 minutes.</p>
        </div>
        <form method="post" class="otp-form">
          <input type="hidden" name="checkout_action" value="verify_otp">
          <input name="otp_code" inputmode="numeric" maxlength="6" pattern="\d{6}" required placeholder="123456">
          <button class="btn" type="submit">Verify OTP & Pay</button>
        </form>
        <form method="post">
          <input type="hidden" name="checkout_action" value="resend_otp">
          <button class="btn secondary" type="submit">Resend OTP</button>
        </form>
      </section>
    <?php endif; ?>
  </div>
</main>
<script src="js/checkout.js?v=20260702-card-mask"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
