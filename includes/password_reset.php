<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mail.php';

function clearPasswordResetSession(): void
{
    unset(
        $_SESSION['reset_email'],
        $_SESSION['reset_otp'],
        $_SESSION['reset_otp_expiry'],
        $_SESSION['reset_verified'],
        $_SESSION['reset_customer_name']
    );
}

function findCustomerByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare("SELECT user_id, full_name, email FROM users WHERE email = ? AND role = 'customer' LIMIT 1");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    return $customer ?: null;
}

function createPasswordResetOtp(PDO $pdo, string $email): array
{
    $customer = findCustomerByEmail($pdo, $email);
    if (!$customer) {
        return [
            'ok' => false,
            'error' => 'No customer account found with this email.',
            'mail' => null,
        ];
    }

    $otp = (string) random_int(100000, 999999);
    $_SESSION['reset_email'] = $customer['email'];
    $_SESSION['reset_customer_name'] = $customer['full_name'];
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_otp_expiry'] = time() + 300;
    $_SESSION['reset_verified'] = false;

    $mail = novatech_send_password_reset_otp_email($customer['email'], $customer['full_name'], $otp);
    $_SESSION['reset_mail_message'] = $mail['sent']
        ? 'Password reset OTP has been sent to your email.'
        : 'Password reset OTP was saved to demo outbox because SMTP sending is not available.';

    return [
        'ok' => true,
        'error' => '',
        'mail' => $mail,
    ];
}

function resetOtpHasExpired(): bool
{
    return empty($_SESSION['reset_otp_expiry']) || time() > (int) $_SESSION['reset_otp_expiry'];
}
