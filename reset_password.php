<?php
$page_title = 'Reset Password | NovaTech Gadgets';
$hide_chatbot = true;
require_once __DIR__ . '/includes/password_reset.php';
require_once __DIR__ . '/includes/password.php';

$error = '';

if (empty($_SESSION['reset_email']) || empty($_SESSION['reset_verified'])) {
    redirect('forgot_password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password === '') {
        $error = 'New password cannot be empty.';
    } elseif (!validatePasswordStrength($password)) {
        $error = passwordStrengthMessage();
    } elseif ($password !== $confirm) {
        $error = 'Confirm password must match.';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'customer'");
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $_SESSION['reset_email']]);
        clearPasswordResetSession();
        $_SESSION['login_success'] = 'Password reset successful. Please login with your new password.';
        redirect('login.php?role=customer');
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main class="section login-entry-page">
  <div class="container auth-layout">
    <section class="panel auth-card login-choice-card">
      <a class="login-back-link" href="login.php">Back to Login</a>
      <h1>Reset password</h1>
      <p>Create a stronger customer account password for NovaTech Gadgets.</p>
      <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
      <form method="post" class="login-form-stack">
        <div class="field"><label>New password</label><input id="reset-password" name="password" type="password" required autocomplete="new-password"></div>
        <ul class="password-rules" data-password-rules="reset-password">
          <li data-rule="length">At least 8 characters</li>
          <li data-rule="upper">At least 1 uppercase letter</li>
          <li data-rule="lower">At least 1 lowercase letter</li>
          <li data-rule="digits">At least 6 numbers</li>
          <li data-rule="spaces">No spaces</li>
        </ul>
        <div class="field"><label>Confirm new password</label><input name="confirm_password" type="password" required autocomplete="new-password"></div>
        <button class="btn" type="submit">Reset Password</button>
      </form>
    </section>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
