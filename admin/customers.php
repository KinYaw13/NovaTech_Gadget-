<?php
$page_title = 'Customers | NovaTech Gadgets';
$active = 'admin';
$base = '../';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$customers = $pdo->query("SELECT user_id, full_name, email, phone, address, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC")->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Customer management</h1><p>View registered customer accounts.</p></div>
    <div class="admin-shell">
      <?php include __DIR__ . '/nav.php'; ?>
      <section class="panel admin-main">
        <table>
          <thead><tr><th>Name</th
                ><th>Email</th><th>Phone</th><th>Joined</th></tr></thead>
          <tbody>
            <?php foreach ($customers as $customer): ?>
              <tr><td><?= e($customer['full_name']) ?></td><td><?= e($customer['email']) ?></td><td><?= e($customer['phone'] ?? '-') ?></td><td><?= e($customer['created_at']) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

