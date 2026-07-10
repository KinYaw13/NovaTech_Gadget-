<?php
$page_title = 'Warranty Claim | NovaTech Gadgets';
$active = 'profile';
require_once __DIR__ . '/includes/auth.php';
require_customer();

function ensure_warranty_claims_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS warranty_claims (
      id INT AUTO_INCREMENT PRIMARY KEY,
      customer_id INT NOT NULL,
      order_id INT NOT NULL,
      order_item_id INT NULL,
      product_name VARCHAR(180) NOT NULL,
      reason VARCHAR(120) NOT NULL,
      description TEXT NOT NULL,
      image_path VARCHAR(255) NULL,
      status ENUM('Pending','Approved','Rejected','Completed') NOT NULL DEFAULT 'Pending',
      admin_note TEXT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_customer_claims (customer_id, created_at),
      INDEX idx_order_claims (order_id),
      INDEX idx_claim_status (status)
    )");
}

function claim_upload_path(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || (int) ($file['size'] ?? 0) > 3 * 1024 * 1024) {
        return '';
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return '';
    }

    $dir = __DIR__ . '/images/uploads/claims';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $filename = 'claim-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = $dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return '';
    }

    return 'images/uploads/claims/' . $filename;
}

ensure_warranty_claims_schema($pdo);
$customerId = (int) $_SESSION['user']['user_id'];
$orderId = (int) ($_GET['order_id'] ?? ($_POST['order_id'] ?? 0));
$error = '';

$orderStmt = $pdo->prepare('SELECT * FROM orders WHERE order_id = ? AND user_id = ?');
$orderStmt->execute([$orderId, $customerId]);
$order = $orderStmt->fetch();
if (!$order) {
    redirect('profile.php');
}

$itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY order_item_id ASC');
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderItemId = (int) ($_POST['order_item_id'] ?? 0);
    $reason = trim((string) ($_POST['reason'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $selectedItem = null;

    foreach ($items as $item) {
        if ((int) $item['order_item_id'] === $orderItemId) {
            $selectedItem = $item;
            break;
        }
    }

    if (!$selectedItem) {
        $error = 'Please choose a product from this order.';
    } elseif ($reason === '') {
        $error = 'Please choose a claim reason.';
    } elseif ($description === '') {
        $error = 'Please describe the issue.';
    } else {
        $imagePath = claim_upload_path($_FILES['claim_image'] ?? []);
        $stmt = $pdo->prepare("INSERT INTO warranty_claims (customer_id, order_id, order_item_id, product_name, reason, description, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$customerId, $orderId, $orderItemId, $selectedItem['product_name'], $reason, $description, $imagePath ?: null]);
        redirect('profile.php?claim=submitted');
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container auth-layout">
    <form class="panel auth-card edit-profile-card" method="post" enctype="multipart/form-data">
      <a class="login-back-link" href="profile.php">Back to Profile</a>
      <h1>Claim Warranty / Request Return</h1>
      <p>Submit a request for order #<?= (int) $order['order_id'] ?>. Admin will review and update the claim status.</p>
      <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
      <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
      <div class="field">
        <label>Product / order item</label>
        <select name="order_item_id" required>
          <option value="">Select product</option>
          <?php foreach ($items as $item): ?>
            <option value="<?= (int) $item['order_item_id'] ?>"><?= e($item['product_name']) ?> x <?= (int) $item['quantity'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Reason</label>
        <select name="reason" required>
          <option value="">Select reason</option>
          <option>Warranty claim</option>
          <option>Defective item</option>
          <option>Wrong item received</option>
          <option>Return request</option>
          <option>Other</option>
        </select>
      </div>
      <div class="field"><label>Description</label><textarea name="description" rows="5" required placeholder="Explain what happened..."></textarea></div>
      <div class="field"><label>Optional image</label><input type="file" name="claim_image" accept="image/png,image/jpeg,image/webp"><small class="field-hint">Optional. JPG, PNG, or WEBP up to 3MB.</small></div>
      <div class="form-actions">
        <button class="btn" type="submit">Submit Claim</button>
        <a class="btn secondary" href="profile.php">Cancel</a>
      </div>
    </form>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
