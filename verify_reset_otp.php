<?php
$page_title = 'Verify Reset OTP | NovaTech Gadgets';
$hide_chatbot = true;
require_once __DIR__ . '/includes/password_reset.php';

$error = '';
$notice = $_SESSION['reset_mail_message'] ?? '';
unset($_SESSION['reset_mail_message']);

if (empty($_SESSION['reset_email'])) {
    redirect('forgot_password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'verify';

    if ($action === 'resend') {
        $result = createPasswordResetOtp($pdo, (string) $_SESSION['reset_email']);
        if ($result['ok']) {
            $notice = 'A new OTP has been sent to your email.';
        } else {
            $error = $result['error'];
        }
    } else {
        $otp = trim($_POST['otp'] ?? '');

        if ($otp === '') {
            $error = 'OTP cannot be empty.';
        } elseif (!preg_match('/^\d{6}$/', $otp)) {
            $error = 'OTP must be 6 digits.';
        } elseif (resetOtpHasExpired()) {
            $error = 'OTP has expired. Please request a new OTP.';
        } elseif (!hash_equals((string) ($_SESSION['reset_otp'] ?? ''), $otp)) {
            $error = 'Invalid OTP. Please try again.';
        } else {
            $_SESSION['reset_verified'] = true;
            redirect('reset_password.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main class="section login-entry-page">
  <div class="container auth-layout">
    <section class="panel auth-card login-choice-card">
      <a class="login-back-link" href="forgot_password.php">Back</a>
      <h1>Verify OTP</h1>
      <p>Enter the 6-digit OTP sent to <?= e((string) $_SESSION['reset_email']) ?>. It is valid for 5 minutes.</p>
      <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($notice): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
      <form method="post" class="login-form-stack">
        <div class="field"><label>OTP code</label><input name="otp" inputmode="numeric" pattern="\d{6}" maxlength="6" required autocomplete="one-time-code"></div>
        <button class="btn" type="submit">Verify OTP</button>
      </form>
      <form method="post" class="guest-login-form">
        <input type="hidden" name="action" value="resend">
        <button class="btn secondary" type="submit">Resend OTP</button>
      </form>
    </section>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
