<?php
$page_title = 'Login | NovaTech Gadgets';
$hide_chatbot = true;
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = $_SESSION['login_success'] ?? '';
unset($_SESSION['login_success']);
$mode = $_POST['role'] ?? ($_GET['role'] ?? 'customer');
$mode = $mode === 'admin' ? 'admin' : 'customer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'guest') {
        login_guest();
        redirect('home.php');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $mode = ($_POST['role'] ?? 'customer') === 'admin' ? 'admin' : 'customer';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
    $stmt->execute([$email, $mode]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        login_user($user);
        redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'home.php');
    }
    $error = $mode === 'admin' ? 'Invalid admin email or password.' : 'Invalid customer email or password.';
}

require_once __DIR__ . '/includes/header.php';
?>
<main class="section login-entry-page">
  <div class="container auth-layout">
    <section class="panel auth-card login-choice-card">
      <a class="login-back-link" href="index.php">Back to Landing</a>
      <h1>Welcome to NovaTech</h1>
      <p>Choose the correct access type to enter the gadget store experience.</p>
      <div class="login-tabs" role="tablist" aria-label="Login role">
        <a class="<?= $mode === 'customer' ? 'active' : '' ?>" href="login.php?role=customer">Customer</a>
        <a class="<?= $mode === 'admin' ? 'active' : '' ?>" href="login.php?role=admin">Admin</a>
      </div>
      <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="notice success"><?= e($success) ?></div><?php endif; ?>
      <form method="post" class="login-form-stack">
        <input type="hidden" name="role" value="<?= e($mode) ?>">
        <div class="field"><label><?= $mode === 'admin' ? 'Admin email' : 'Customer email' ?></label><input name="email" type="email" required autocomplete="email"></div>
        <div class="field"><label>Password</label><input name="password" type="password" required autocomplete="current-password"></div>
        <?php if ($mode === 'customer'): ?>
          <a class="auth-inline-link" href="forgot_password.php">Forgot Password?</a>
        <?php endif; ?>
        <button class="btn" type="submit"><?= $mode === 'admin' ? 'Login as Admin' : 'Login / Get Started' ?></button>
      </form>
      <form method="post" class="guest-login-form">
        <input type="hidden" name="action" value="guest">
        <button class="btn secondary" type="submit">Continue as Guest</button>
      </form>
      <p class="login-small-note">Guests can browse products and use the AI Gadget Finder. Checkout and profile actions require customer login.</p>
      <a class="btn ghost" href="register.php">Create customer account</a>
    </section>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
