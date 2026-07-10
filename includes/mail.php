<?php
declare(strict_types=1);

function novatech_mail_config(): array
{
    return require __DIR__ . '/mail_config.php';
}

function novatech_outbox_dir(): string
{
    $dir = dirname(__DIR__) . '/storage/mail_outbox';
    if ((is_dir($dir) || @mkdir($dir, 0775, true)) && is_writable($dir)) {
        return $dir;
    }

    $fallback = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'novatech_mail_outbox';
    if (!is_dir($fallback)) {
        @mkdir($fallback, 0775, true);
    }
    return $fallback;
}

function novatech_write_demo_mail(string $to, string $subject, string $html): string
{
    $safeSubject = preg_replace('/[^a-z0-9]+/i', '-', strtolower($subject));
    $file = novatech_outbox_dir() . '/' . date('Ymd-His') . '-' . trim((string) $safeSubject, '-') . '.html';
    $body = "<!-- Demo outbox email. Configure Gmail SMTP in includes/mail_config.php to send for real. -->\n"
        . "<p><strong>To:</strong> " . htmlspecialchars($to, ENT_QUOTES, 'UTF-8') . "</p>\n"
        . "<p><strong>Subject:</strong> " . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . "</p>\n"
        . $html;
    if (@file_put_contents($file, $body) === false) {
        $file = tempnam(sys_get_temp_dir(), 'novatech-mail-') ?: '';
        if ($file !== '') {
            @file_put_contents($file, $body);
        }
    }
    return $file;
}

function novatech_load_phpmailer(): bool
{
    $root = dirname(__DIR__);
    $autoload = $root . '/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }

    $phpMailer = $root . '/PHPMailer/src/PHPMailer.php';
    $smtp = $root . '/PHPMailer/src/SMTP.php';
    $exception = $root . '/PHPMailer/src/Exception.php';
    if (is_file($phpMailer) && is_file($smtp) && is_file($exception)) {
        require_once $exception;
        require_once $phpMailer;
        require_once $smtp;
    }

    return class_exists('PHPMailer\\PHPMailer\\PHPMailer');
}

function novatech_send_mail(string $to, string $subject, string $html): array
{
    $config = novatech_mail_config();
    $configured = (bool) ($config['configured'] ?? ($config['username'] !== '' && $config['password'] !== ''));
    $canSend = $configured && $config['from_email'] !== '' && novatech_load_phpmailer();

    if (!$canSend) {
        $file = novatech_write_demo_mail($to, $subject, $html);
        return [
            'sent' => false,
            'demo_file' => $file,
            'message' => $config['warning'] ?? 'Email saved to demo outbox because SMTP/PHPMailer is not configured.',
        ];
    }

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) $config['port'];
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html)));
        $mail->send();

        return ['sent' => true, 'demo_file' => '', 'message' => 'Email sent.'];
    } catch (Throwable $exception) {
        $file = novatech_write_demo_mail($to, $subject, $html);
        return [
            'sent' => false,
            'demo_file' => $file,
            'message' => 'SMTP failed, so the email was saved to demo outbox.',
        ];
    }
}

function novatech_send_otp_email(string $to, string $name, string $otp): array
{
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeOtp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');
    $html = <<<HTML
<div style="font-family:Arial,sans-serif;max-width:620px;margin:auto;color:#111827">
  <h2 style="color:#0f172a">NovaTech Gadgets payment OTP</h2>
  <p>Hello {$safeName},</p>
  <p>Your 6-digit OTP is:</p>
  <div style="font-size:30px;font-weight:800;letter-spacing:8px;background:#f5f7fb;border:1px solid #e5e7eb;border-radius:16px;padding:18px;text-align:center">{$safeOtp}</div>
  <p>This OTP expires in 5 minutes. Do not share it with anyone.</p>
  <p style="color:#64748b">This is a student assignment demo payment verification email.</p>
</div>
HTML;

    return novatech_send_mail($to, 'NovaTech Gadgets payment OTP', $html);
}

function generatePasswordResetOtpEmail(string $customerName, string $otp): string
{
    $safeName = htmlspecialchars($customerName !== '' ? $customerName : 'NovaTech customer', ENT_QUOTES, 'UTF-8');
    $safeOtp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<div style="margin:0;padding:28px;background:#f5f5f7;font-family:Arial,Helvetica,sans-serif;color:#111111">
  <div style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #e5e5ea;border-radius:24px;overflow:hidden">
    <div style="background:#111111;color:#ffffff;padding:24px 28px">
      <h1 style="margin:0;font-size:22px;letter-spacing:-0.02em">NovaTech Gadgets</h1>
      <p style="margin:6px 0 0;color:#c7d2fe;font-size:14px">Password Reset Verification</p>
    </div>
    <div style="padding:30px 28px">
      <p style="margin:0 0 14px;font-size:16px;line-height:1.6">Hello {$safeName},</p>
      <p style="margin:0 0 20px;font-size:16px;line-height:1.6;color:#3f3f46">Use this 6-digit OTP to reset your NovaTech Gadgets customer account password.</p>
      <div style="margin:24px 0;padding:20px;border-radius:18px;background:#111111;color:#ffffff;text-align:center;font-size:34px;font-weight:800;letter-spacing:10px">{$safeOtp}</div>
      <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3f3f46">This OTP is valid for <strong>5 minutes only</strong>.</p>
      <p style="margin:0;padding:14px 16px;border-radius:14px;background:#f5f5f7;color:#52525b;font-size:14px;line-height:1.6">Security warning: Do not share this OTP with anyone. NovaTech Gadgets will never ask for your password or OTP outside this reset flow.</p>
    </div>
    <div style="padding:18px 28px;border-top:1px solid #e5e5ea;color:#71717a;font-size:13px;line-height:1.5">
      This is an automated email. Please do not reply.
    </div>
  </div>
</div>
HTML;
}

function novatech_send_password_reset_otp_email(string $to, string $name, string $otp): array
{
    return novatech_send_mail($to, 'NovaTech Gadgets Password Reset OTP', generatePasswordResetOtpEmail($name, $otp));
}

function invoice_value(array $row, string $key, string $fallback = '-'): string
{
    $value = trim((string) ($row[$key] ?? ''));
    return $value !== '' ? $value : $fallback;
}

function novatech_invoice_html(array $order, array $items): string
{
    $rows = '';
    $warrantyStart = htmlspecialchars((string) $order['created_at'], ENT_QUOTES, 'UTF-8');
    foreach ($items as $item) {
        $product = htmlspecialchars((string) $item['product_name'], ENT_QUOTES, 'UTF-8');
        $category = htmlspecialchars(invoice_value($item, 'product_category'), ENT_QUOTES, 'UTF-8');
        $qty = (int) $item['quantity'];
        $unit = money((float) $item['unit_price']);
        $line = money((float) $item['subtotal']);
        $warrantyPeriod = htmlspecialchars(invoice_value($item, 'warranty_period', '1 Year'), ENT_QUOTES, 'UTF-8');
        $warrantyType = htmlspecialchars(invoice_value($item, 'warranty_type', 'Manufacturer Warranty'), ENT_QUOTES, 'UTF-8');
        $warrantyCoverage = htmlspecialchars(invoice_value($item, 'warranty_description', 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.'), ENT_QUOTES, 'UTF-8');
        $warrantyLine = "{$warrantyPeriod} {$warrantyType}<br><span style=\"color:#64748b\">Starts from purchase date</span><br><span style=\"color:#64748b\">Start date: {$warrantyStart}</span><br><span style=\"color:#64748b\">Coverage: {$warrantyCoverage}</span>";
        $rows .= "<tr>"
            . "<td style=\"padding:12px;border-bottom:1px solid #e5e7eb;vertical-align:top\"><strong>{$product}</strong><br><span style=\"color:#64748b\">{$category}</span></td>"
            . "<td style=\"padding:12px;border-bottom:1px solid #e5e7eb;text-align:center;vertical-align:top\">{$qty}</td>"
            . "<td style=\"padding:12px;border-bottom:1px solid #e5e7eb;text-align:right;vertical-align:top\">{$unit}</td>"
            . "<td style=\"padding:12px;border-bottom:1px solid #e5e7eb;text-align:right;vertical-align:top\">{$line}</td>"
            . "<td style=\"padding:12px;border-bottom:1px solid #e5e7eb;vertical-align:top;line-height:1.45\">{$warrantyLine}</td>"
            . "</tr>";
    }

    $invoice = htmlspecialchars((string) $order['invoice_no'], ENT_QUOTES, 'UTF-8');
    $date = htmlspecialchars((string) $order['created_at'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars((string) $order['customer_name'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars((string) $order['customer_email'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars((string) $order['customer_phone'], ENT_QUOTES, 'UTF-8');
    $address = nl2br(htmlspecialchars((string) $order['shipping_address'], ENT_QUOTES, 'UTF-8'));
    $method = htmlspecialchars((string) $order['payment_method'], ENT_QUOTES, 'UTF-8');
    $bank = htmlspecialchars((string) ($order['bank_name'] ?: '-'), ENT_QUOTES, 'UTF-8');
    $last4 = htmlspecialchars((string) ($order['card_last4'] ?: '-'), ENT_QUOTES, 'UTF-8');
    $maskedCard = $last4 !== '-' ? "**** **** **** {$last4}" : '-';
    $accountHolder = htmlspecialchars((string) (($order['account_holder_name'] ?? '') !== '' ? $order['account_holder_name'] : '-'), ENT_QUOTES, 'UTF-8');
    $paymentReference = htmlspecialchars((string) (($order['payment_reference'] ?? '') !== '' ? $order['payment_reference'] : '-'), ENT_QUOTES, 'UTF-8');
    $deliveryMethod = htmlspecialchars((string) ($order['delivery_method'] ?? 'Standard Delivery'), ENT_QUOTES, 'UTF-8');
    $deliveryFee = htmlspecialchars((string) ($order['delivery_fee_display'] ?? 'RM 0.00'), ENT_QUOTES, 'UTF-8');
    $deliveryEstimate = htmlspecialchars((string) ($order['delivery_estimate'] ?? '-'), ENT_QUOTES, 'UTF-8');
    $pickupLocation = htmlspecialchars((string) ($order['pickup_location'] ?? ''), ENT_QUOTES, 'UTF-8');
    $pickupRow = $pickupLocation !== '' ? "<p style=\"margin:0\"><strong>Pickup location:</strong> {$pickupLocation}</p>" : '';
    $status = htmlspecialchars((string) ($order['payment_status'] ?? 'Paid'), ENT_QUOTES, 'UTF-8');
    $voucherCode = htmlspecialchars((string) ($order['voucher_code'] ?? ''), ENT_QUOTES, 'UTF-8');
    $discountDisplay = htmlspecialchars((string) ($order['discount_display'] ?? ''), ENT_QUOTES, 'UTF-8');
    $voucherRow = $voucherCode !== '' ? "<p style=\"margin:0 0 8px\">Voucher ({$voucherCode}): <strong>-{$discountDisplay}</strong></p>" : '';

    return <<<HTML
<div style="font-family:Arial,sans-serif;background:#f3f4f6;padding:24px;color:#111827">
  <div style="max-width:920px;margin:auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:18px;padding:28px">
    <div style="border-bottom:2px solid #111827;padding-bottom:18px;margin-bottom:22px">
      <h1 style="margin:0;color:#000000;font-size:28px;letter-spacing:-0.02em">NovaTech Gadgets</h1>
      <p style="margin:6px 0 0;color:#64748b">Payment invoice and warranty record</p>
    </div>

    <table style="width:100%;border-collapse:collapse;margin-bottom:22px">
      <tr>
        <td style="padding:0 16px 0 0;vertical-align:top;width:50%">
          <p style="margin:0 0 8px"><strong>Invoice number:</strong> {$invoice}</p>
          <p style="margin:0 0 8px"><strong>Invoice date:</strong> {$date}</p>
          <p style="margin:0 0 8px"><strong>Payment status:</strong> {$status}</p>
        </td>
        <td style="padding:0;vertical-align:top;width:50%">
          <p style="margin:0 0 8px"><strong>Customer name:</strong> {$name}</p>
          <p style="margin:0 0 8px"><strong>Customer email:</strong> {$email}</p>
          <p style="margin:0 0 8px"><strong>Customer phone:</strong> {$phone}</p>
          <p style="margin:0"><strong>Shipping address:</strong><br>{$address}</p>
        </td>
      </tr>
    </table>

    <table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden">
      <thead>
        <tr style="background:#111827;color:#ffffff">
          <th style="padding:12px;text-align:left">Product / Category</th>
          <th style="padding:12px;text-align:center">Qty</th>
          <th style="padding:12px;text-align:right">Unit price</th>
          <th style="padding:12px;text-align:right">Subtotal</th>
          <th style="padding:12px;text-align:left">Warranty</th>
        </tr>
      </thead>
      <tbody>{$rows}</tbody>
    </table>

    <table style="width:100%;border-collapse:collapse;margin-top:22px">
      <tr>
        <td style="vertical-align:top;width:55%">
          <p style="margin:0 0 8px"><strong>Payment method:</strong> {$method}</p>
          <p style="margin:0 0 8px"><strong>Bank name:</strong> {$bank}</p>
          <p style="margin:0 0 8px"><strong>Masked card number:</strong> {$maskedCard}</p>
          <p style="margin:0 0 8px"><strong>Account holder:</strong> {$accountHolder}</p>
          <p style="margin:0 0 16px"><strong>Payment reference:</strong> {$paymentReference}</p>
          <p style="margin:0 0 8px"><strong>Delivery method:</strong> {$deliveryMethod}</p>
          <p style="margin:0 0 8px"><strong>Delivery estimate:</strong> {$deliveryEstimate}</p>
          {$pickupRow}
        </td>
        <td style="vertical-align:top;text-align:right;width:45%">
          <p style="margin:0 0 8px">Subtotal: <strong>{$order['subtotal_display']}</strong></p>
          {$voucherRow}
          <p style="margin:0 0 8px">Delivery fee: <strong>{$deliveryFee}</strong></p>
          <p style="margin:0 0 8px">Tax / service fee: <strong>{$order['tax_display']}</strong></p>
          <p style="margin:0;font-size:20px">Total: <strong>{$order['total_display']}</strong></p>
        </td>
      </tr>
    </table>

    <p style="margin:22px 0 0;color:#64748b;line-height:1.5">Warranty starts from purchase date. Please keep this invoice as proof of purchase for warranty support. Warranty coverage is based on the product warranty terms shown above.</p>
  </div>
</div>
HTML;
}

function novatech_send_invoice_email(string $to, array $order, array $items): array
{
    return novatech_send_mail($to, 'NovaTech Gadgets invoice ' . $order['invoice_no'], novatech_invoice_html($order, $items));
}
