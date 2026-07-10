<?php
$page_title = 'Messages | NovaTech Gadgets';
$active = 'messages';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/messages.php';
require_customer();
ensure_customer_engagement_schema($pdo);

$customerId = (int) $_SESSION['user']['user_id'];
$adminId = admin_user_id($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim((string) ($_POST['message'] ?? ''));
    if ($message === '') {
        $error = 'Please enter a message.';
    } else {
        create_message($pdo, $customerId, $customerId, 'customer', $adminId, 'admin', 'Customer Reply', $message, $adminId);
        redirect('customer_messages.php');
    }
}

$pdo->prepare("UPDATE messages SET is_read = 1 WHERE customer_id = ? AND receiver_role = 'customer'")->execute([$customerId]);
$stmt = $pdo->prepare('SELECT * FROM messages WHERE customer_id = ? ORDER BY created_at ASC');
$stmt->execute([$customerId]);
$messages = $stmt->fetchAll();
$voucherStmt = $pdo->prepare("SELECT * FROM vouchers WHERE customer_id = ? AND status = 'active' AND expires_at >= NOW() ORDER BY created_at DESC");
$voucherStmt->execute([$customerId]);
$vouchers = $voucherStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Messages</h1><p>Your private one-to-one conversation with NovaTech admin.</p></div>
    <?php if ($vouchers): ?>
      <div class="voucher-strip">
        <?php foreach ($vouchers as $voucher): ?>
          <div class="panel voucher-card"><strong><?= e($voucher['code']) ?></strong><span>RM <?= number_format((float) $voucher['discount_value'], 2) ?> off</span><small>Expires <?= e($voucher['expires_at']) ?></small></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <section class="panel message-panel">
      <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
      <div class="message-thread">
        <?php foreach ($messages as $row): ?>
          <?php $mine = $row['sender_role'] === 'customer'; ?>
          <article class="message-bubble <?= $mine ? 'mine' : 'theirs' ?>">
            <strong><?= e($row['subject'] ?: ($mine ? 'You' : 'NovaTech Admin')) ?></strong>
            <p><?= nl2br(e($row['message'])) ?></p>
            <small><?= e($row['created_at']) ?> · <?= $row['is_read'] ? 'Read' : 'Unread' ?></small>
          </article>
        <?php endforeach; ?>
        <?php if (!$messages): ?>
          <div class="empty-state compact">
            <h3>No messages yet</h3>
            <p>Your private messages with NovaTech admin will appear here.</p>
          </div>
        <?php endif; ?>
      </div>
      <form method="post" class="message-compose">
        <textarea name="message" rows="3" placeholder="Reply to NovaTech admin..." required></textarea>
        <button class="btn" type="submit">Send Message</button>
      </form>
    </section>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
