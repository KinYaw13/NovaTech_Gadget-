<?php
$page_title = 'Forgot Password | NovaTech Gadgets';
$hide_chatbot = true;
require_once __DIR__ . '/includes/password_reset.php';

$error = '';
$notice = '';
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($email === '') {
        $error = 'Email cannot be empty.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $result = createPasswordResetOtp($pdo, $email);
        if (!$result['ok']) {
            $error = $result['error'];
        } else {
            redirect('verify_reset_otp.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main class="section login-entry-page">
  <div class="container auth-layout">
    <section class="panel auth-card login-choice-card">
      <a class="login-back-link" href="login.php">Back to Login</a>
      <h1>Forgot password</h1>
      <p>Enter your customer account email. We will send a 6-digit OTP to verify your reset request.</p>
      <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($notice): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
      <form method="post" class="login-form-stack">
        <div class="field"><label>Customer email</label><input name="email" type="email" value="<?= e($email) ?>" required autocomplete="email"></div>
        <button class="btn" type="submit">Send OTP</button>
      </form>
    </section>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
