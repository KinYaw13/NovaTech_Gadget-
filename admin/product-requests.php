<?php
$page_title = 'Product Requests | NovaTech Gadgets';
$active = 'admin';
$base = '../';
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../chatbot_request.php';

ensure_product_requests_schema($pdo);

$allowedStatuses = ['Pending', 'Approved', 'Rejected', 'Added'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['request_id'] ?? 0);
    $status = $_POST['status'] ?? 'Pending';
    $note = trim((string) ($_POST['admin_note'] ?? ''));
    if ($id > 0 && in_array($status, $allowedStatuses, true)) {
        $stmt = $pdo->prepare('UPDATE product_requests SET status = ?, admin_note = ? WHERE id = ?');
        $stmt->execute([$status, $note, $id]);
    }
    redirect('product-requests.php');
}

$requests = $pdo->query('SELECT pr.*, u.full_name AS customer_name, u.email AS account_email
    FROM product_requests pr
    LEFT JOIN users u ON u.user_id = pr.customer_id
    ORDER BY pr.created_at DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>AI product requests</h1><p>Review chatbot product requests before anything is added to the store.</p></div>
    <div class="admin-shell">
      <?php include __DIR__ . '/nav.php'; ?>
      <section class="panel admin-main">
        <div class="table-scroll">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Requested product</th>
                <th>Customer</th>
                <th>Expected price</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($requests as $request): ?>
                <tr>
                  <td>#<?= (int) $request['id'] ?></td>
                  <td>
                    <strong><?= e($request['product_name']) ?></strong><br>
                    <small><?= e($request['brand'] ?: 'Unknown brand') ?> · <?= e($request['category'] ?: 'Accessories') ?></small>
                    <div class="request-meta">
                      <span>Original: <?= e($request['customer_message']) ?></span>
                      <?php if (!empty($request['normalized_query'])): ?><span>Normalized: <?= e($request['normalized_query']) ?></span><?php endif; ?>
                      <?php if (!empty($request['description'])): ?><span>Notes: <?= e($request['description']) ?></span><?php endif; ?>
                    </div>
                  </td>
                  <td>
                    <?= e($request['customer_name'] ?: 'Guest / Unknown') ?><br>
                    <small><?= e($request['requested_by_email'] ?: ($request['account_email'] ?? '-')) ?></small>
                  </td>
                  <td><?= money($request['estimated_price']) ?></td>
                  <td><span class="badge <?= strtolower((string) $request['status']) ?>"><?= e($request['status']) ?></span></td>
                  <td><?= e(date('Y-m-d H:i', strtotime((string) $request['created_at']))) ?></td>
                  <td>
                    <form method="post" class="request-action-form">
                      <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                      <div class="request-action-row">
                        <button class="btn secondary" type="submit" name="status" value="Approved">Approve</button>
                        <button class="btn secondary" type="submit" name="status" value="Rejected">Reject</button>
                        <button class="btn secondary" type="submit" name="status" value="Added">Mark Added</button>
                      </div>
                      <textarea name="admin_note" placeholder="Admin note"><?= e($request['admin_note'] ?? '') ?></textarea>
                    </form>
                    <?php if ($request['status'] === 'Approved'): ?>
                      <a class="btn" href="add-product.php?request_id=<?= (int) $request['id'] ?>">Add to Product</a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$requests): ?>
                <tr><td class="empty-table-cell" colspan="7">
                  <div class="empty-state compact">
                    <h3>No product requests</h3>
                    <p>AI Gadget Finder requests will appear here after customers ask for unavailable gadgets with an expected price.</p>
                  </div>
                </td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
