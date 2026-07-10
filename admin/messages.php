<?php
$page_title = 'Admin Messages | NovaTech Gadgets';
$active = 'admin';
$base = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/messages.php';
require_admin();
ensure_customer_engagement_schema($pdo);

$adminId = (int) $_SESSION['user']['user_id'];
$selectedCustomerId = (int) ($_GET['customer_id'] ?? 0);
$search = trim((string) ($_GET['q'] ?? ''));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedCustomerId = (int) ($_POST['customer_id'] ?? 0);
    $message = trim((string) ($_POST['message'] ?? ''));
    if ($selectedCustomerId <= 0) {
        $error = 'Please choose a customer before sending.';
    } elseif ($message === '') {
        $error = 'Please enter a message.';
    } else {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND role = 'customer'");
        $check->execute([$selectedCustomerId]);
        if ($check->fetch()) {
            create_message($pdo, $selectedCustomerId, $adminId, 'admin', $selectedCustomerId, 'customer', 'NovaTech Admin Reply', $message, $adminId);
            redirect('messages.php?customer_id=' . $selectedCustomerId);
        }
        $error = 'Customer not found.';
    }
}

$where = "WHERE u.role = 'customer'";
$params = [];
if ($search !== '') {
    $where .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
}

$customerSql = "
SELECT u.user_id, u.full_name, u.email, u.phone,
  (SELECT COUNT(*) FROM messages m WHERE m.customer_id = u.user_id AND m.receiver_role = 'admin' AND m.is_read = 0) AS unread_count,
  (SELECT m.message FROM messages m WHERE m.customer_id = u.user_id ORDER BY m.created_at DESC LIMIT 1) AS latest_message,
  (SELECT m.created_at FROM messages m WHERE m.customer_id = u.user_id ORDER BY m.created_at DESC LIMIT 1) AS latest_time
FROM users u
{$where}
ORDER BY COALESCE(latest_time, u.created_at) DESC";
$customerStmt = $pdo->prepare($customerSql);
$customerStmt->execute($params);
$customers = $customerStmt->fetchAll();

if ($selectedCustomerId === 0 && $customers) {
    $selectedCustomerId = (int) $customers[0]['user_id'];
}

$selected = null;
$messages = [];
if ($selectedCustomerId > 0) {
    $selectedStmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'customer'");
    $selectedStmt->execute([$selectedCustomerId]);
    $selected = $selectedStmt->fetch();
    if ($selected) {
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE customer_id = ? AND receiver_role = 'admin'")->execute([$selectedCustomerId]);
        $messageStmt = $pdo->prepare('SELECT * FROM messages WHERE customer_id = ? ORDER BY created_at ASC');
        $messageStmt->execute([$selectedCustomerId]);
        $messages = $messageStmt->fetchAll();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Customer messages</h1><p>Select any registered customer and chat privately.</p></div>
    <div class="admin-shell">
      <?php include __DIR__ . '/nav.php'; ?>
      <section class="admin-main message-admin-grid">
        <aside class="panel admin-customer-list">
          <form method="get" class="message-search">
            <input name="q" value="<?= e($search) ?>" placeholder="Search name, email, phone">
            <button class="btn secondary" type="submit">Search</button>
          </form>
          <?php foreach ($customers as $customer): ?>
            <a class="customer-message-row <?= (int) $customer['user_id'] === $selectedCustomerId ? 'active' : '' ?>" href="messages.php?customer_id=<?= (int) $customer['user_id'] ?>&q=<?= urlencode($search) ?>">
              <strong><?= e($customer['full_name']) ?></strong>
              <span><?= e($customer['email']) ?></span>
              <small><?= e($customer['phone'] ?: 'No phone saved') ?></small>
              <em><?= e(substr((string) ($customer['latest_message'] ?? 'No messages yet'), 0, 70)) ?></em>
              <time><?= e((string) ($customer['latest_time'] ?? '')) ?></time>
              <?php if ((int) $customer['unread_count'] > 0): ?><b><?= (int) $customer['unread_count'] ?></b><?php endif; ?>
            </a>
          <?php endforeach; ?>
          <?php if (!$customers): ?>
            <div class="empty-state compact">
              <h3>No customers found</h3>
              <p>Try another search term, or wait for customers to register.</p>
            </div>
          <?php endif; ?>
        </aside>
        <section class="panel message-panel">
          <?php if ($selected): ?>
            <div class="message-chat-head"><div><h2><?= e($selected['full_name']) ?></h2><p><?= e($selected['email']) ?></p></div><span class="badge">Private</span></div>
            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
            <div class="message-thread">
              <?php foreach ($messages as $row): ?>
                <?php $mine = $row['sender_role'] === 'admin' || $row['sender_role'] === 'system'; ?>
                <article class="message-bubble <?= $mine ? 'mine' : 'theirs' ?>">
                  <strong><?= e($row['subject'] ?: ($mine ? 'Admin' : 'Customer')) ?></strong>
                  <p><?= nl2br(e($row['message'])) ?></p>
                  <small><?= e($row['created_at']) ?></small>
                </article>
              <?php endforeach; ?>
              <?php if (!$messages): ?>
                <div class="empty-state compact">
                  <h3>No messages yet</h3>
                  <p>Send the first message to start a private conversation with this customer.</p>
                </div>
              <?php endif; ?>
            </div>
            <form method="post" class="message-compose">
              <input type="hidden" name="customer_id" value="<?= (int) $selected['user_id'] ?>">
              <textarea name="message" rows="3" placeholder="Reply to <?= e($selected['full_name']) ?>..." required></textarea>
              <button class="btn" type="submit">Send Message</button>
            </form>
          <?php else: ?>
            <div class="empty-state">
              <h2>No customer selected</h2>
              <p>Select a customer from the list to view or start a private conversation.</p>
            </div>
          <?php endif; ?>
        </section>
      </section>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
