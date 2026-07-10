<?php
$page_title = 'Edit Profile | NovaTech Gadgets';
$active = 'profile';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/profile_helpers.php';
require_once __DIR__ . '/includes/messages.php';
require_customer();

$userId = (int) $_SESSION['user']['user_id'];
$stmt = $pdo->prepare('SELECT user_id, full_name, email, phone, address, age, gender, role FROM users WHERE user_id = ? AND role = ? LIMIT 1');
$stmt->execute([$userId, 'customer']);
$profile = $stmt->fetch();

if (!$profile) {
    redirect('login.php');
}

$error = '';
$fullName = trim($_POST['full_name'] ?? (string) $profile['full_name']);
$email = trim($_POST['email'] ?? (string) $profile['email']);
$storedPhone = (string) ($profile['phone'] ?? '');
$phoneInput = trim($_POST['phone'] ?? preg_replace('/^\+60/', '', $storedPhone));
$countryCode = $_POST['country_code'] ?? '+60';
$address = trim($_POST['address'] ?? (string) ($profile['address'] ?? ''));
$age = trim($_POST['age'] ?? (string) ($profile['age'] ?? ''));
$gender = trim($_POST['gender'] ?? (string) ($profile['gender'] ?? ''));
$allowedGenders = ['', 'Female', 'Male', 'Non-binary', 'Prefer not to say'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $normalizedPhone = normalizeMalaysiaPhone($countryCode, $phoneInput);

    if ($fullName === '') {
        $error = 'Full name cannot be empty.';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email format is invalid.';
    } elseif ($normalizedPhone === null) {
        $error = 'Please enter a valid phone number.';
    } elseif ($age !== '' && (!ctype_digit($age) || (int) $age < 13 || (int) $age > 120)) {
        $error = 'Please enter a valid age.';
    } elseif (!in_array($gender, $allowedGenders, true)) {
        $error = 'Please select a valid gender.';
    } else {
        $check = $pdo->prepare('SELECT user_id FROM users WHERE email = ? AND user_id <> ? LIMIT 1');
        $check->execute([$email, $userId]);

        if ($check->fetch()) {
            $error = 'This email is already used by another account.';
        } else {
            try {
                $update = $pdo->prepare('UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, age = ?, gender = ? WHERE user_id = ? AND role = ?');
                $update->execute([$fullName, $email, $normalizedPhone, $address, $age !== '' ? (int) $age : null, $gender !== '' ? $gender : null, $userId, 'customer']);

                $_SESSION['user']['full_name'] = $fullName;
                $_SESSION['user']['email'] = $email;
                update_profile_completion($pdo, $userId);

                redirect('profile.php?updated=1');
            } catch (PDOException $exception) {
                $error = 'Profile update failed. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container auth-layout">
    <form class="panel auth-card edit-profile-card" method="post">
      <a class="login-back-link" href="profile.php">Back to Profile</a>
      <h1>Edit Profile</h1>
      <p>Update your customer details for checkout, invoices, and order records.</p>
      <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
      <div class="field"><label>Full name</label><input name="full_name" value="<?= e($fullName) ?>" required autocomplete="name"></div>
      <div class="field"><label>Email address</label><input name="email" type="email" value="<?= e($email) ?>" required autocomplete="email"></div>
      <div class="field">
        <label>Phone number</label>
        <div class="phone-input-row">
          <select name="country_code" aria-label="Country code">
            <option value="+60" <?= $countryCode === '+60' ? 'selected' : '' ?>>Malaysia +60</option>
          </select>
          <input name="phone" inputmode="numeric" value="<?= e($phoneInput) ?>" placeholder="123456789" autocomplete="tel-national">
        </div>
        <small class="field-hint">Use 9 to 10 digits after +60. Example: +60123456789.</small>
      </div>
      <div class="profile-form-grid">
        <div class="field"><label>Age</label><input name="age" type="number" min="13" max="120" value="<?= e($age) ?>" placeholder="21"></div>
        <div class="field">
          <label>Gender</label>
          <select name="gender">
            <option value="" <?= $gender === '' ? 'selected' : '' ?>>Select gender</option>
            <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Non-binary" <?= $gender === 'Non-binary' ? 'selected' : '' ?>>Non-binary</option>
            <option value="Prefer not to say" <?= $gender === 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
          </select>
        </div>
      </div>
      <div class="field"><label>Address</label><textarea name="address" rows="4" placeholder="Shipping address"><?= e($address) ?></textarea></div>
      <div class="form-actions">
        <button class="btn" type="submit">Save Changes</button>
        <a class="btn secondary" href="profile.php">Cancel</a>
      </div>
    </form>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
