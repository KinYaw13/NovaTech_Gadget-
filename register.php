<?php
$page_title = 'Register | NovaTech Gadgets';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/password.php';
require_once __DIR__ . '/includes/messages.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid name and email address.';
    } elseif (!validatePasswordStrength($password)) {
        $error = passwordStrengthMessage();
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            send_welcome_profile_message($pdo, (int) $pdo->lastInsertId(), $name);
            redirect('login.php');
        } catch (PDOException $e) {
            $error = 'Email is already registered.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container auth-layout">
    <form class="panel auth-card" method="post">
      <h1>Register</h1>
      <p>Create a customer account for NovaTech Gadgets.</p>
      <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
      <div class="field"><label>Full name</label><input name="full_name" required></div>
      <div class="field"><label>Email</label><input name="email" type="email" required></div>
      <div class="field"><label>Password</label><input id="register-password" name="password" type="password" required autocomplete="new-password"></div>
      <ul class="password-rules" data-password-rules="register-password">
        <li data-rule="length">At least 8 characters</li>
        <li data-rule="upper">At least 1 uppercase letter</li>
        <li data-rule="lower">At least 1 lowercase letter</li>
        <li data-rule="digits">At least 6 numbers</li>
        <li data-rule="spaces">No spaces</li>
      </ul>
      <div class="field"><label>Confirm password</label><input name="confirm_password" type="password" required autocomplete="new-password"></div>
      <button class="btn" type="submit">Register</button>
      <a class="btn ghost" href="login.php">Already have an account?</a>
    </form>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
