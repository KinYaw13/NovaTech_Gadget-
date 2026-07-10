<?php
$page_title = 'Add Product | NovaTech Gadgets';
$active = 'admin';
$base = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/product-image-admin.php';
require_once __DIR__ . '/../chatbot_request.php';
require_admin();

$categories = $pdo->query('SELECT * FROM categories ORDER BY category_name')->fetchAll();
$error = '';
$requestId = (int) ($_GET['request_id'] ?? 0);
$product = [
    'product_name' => '',
    'brand' => '',
    'description' => '',
    'category_id' => 0,
    'price' => 0,
    'stock_quantity' => 0,
    'image' => '',
    'rating' => 4.5,
    'status' => 'active',
];

if ($requestId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    ensure_product_requests_schema($pdo);
    $stmt = $pdo->prepare('SELECT * FROM product_requests WHERE id = ? LIMIT 1');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    if ($request) {
        $categoryId = 0;
        foreach ($categories as $categoryRow) {
            if (strcasecmp((string) $categoryRow['category_name'], (string) ($request['category'] ?? '')) === 0) {
                $categoryId = (int) $categoryRow['category_id'];
                break;
            }
        }
        $product = [
            'product_name' => $request['product_name'] ?? '',
            'brand' => $request['brand'] ?? '',
            'description' => trim((string) ($request['description'] ?? '')) ?: 'Added from approved NovaTech AI product request #' . $requestId . '.',
            'category_id' => $categoryId,
            'price' => (float) ($request['estimated_price'] ?? 0),
            'stock_quantity' => 0,
            'image' => $request['product_image_url'] ?? '',
            'rating' => 4.5,
            'status' => 'active',
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = (int) ($_POST['category_id'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock_quantity'] ?? 0);
    $rating = (float) ($_POST['rating'] ?? 4.5);
    $image = trim($_POST['image_url'] ?? '');
    $product = [
        'product_name' => $name,
        'brand' => $brand,
        'description' => $description,
        'category_id' => $category,
        'price' => $price,
        'stock_quantity' => $stock,
        'image' => $image,
        'rating' => $rating,
        'status' => 'active',
    ];

    if ($name === '' || $brand === '' || $category <= 0 || $price <= 0) {
        $error = 'Please complete all required product fields.';
    } else {
        try {
            $uploadedImage = upload_product_image($_FILES['product_image_file'] ?? [], $name);
            if ($uploadedImage !== '') {
                $image = $uploadedImage;
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO products (category_id, product_name, brand, description, price, stock_quantity, image, rating, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$category, $name, $brand, $description, $price, $stock, $image, $rating]);
            $productId = (int) $pdo->lastInsertId();
            save_product_color_images($pdo, $productId, $_POST, $_FILES);
            if (!empty($_POST['request_id'])) {
                $markAdded = $pdo->prepare("UPDATE product_requests SET status = 'Added', admin_note = CONCAT(COALESCE(admin_note, ''), CASE WHEN COALESCE(admin_note, '') = '' THEN '' ELSE '\n' END, 'Added as product ID ', ?) WHERE id = ?");
                $markAdded->execute([$productId, (int) $_POST['request_id']]);
            }
            $pdo->commit();
            redirect('products.php');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $exception->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="page-title"><h1>Add product</h1><p>Create a new product record.</p></div>
    <div class="admin-shell">
      <?php include __DIR__ . '/nav.php'; ?>
      <form class="panel admin-main" method="post" enctype="multipart/form-data">
        <?php if ($requestId > 0): ?><input type="hidden" name="request_id" value="<?= $requestId ?>"><?php endif; ?>
        <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <?php include __DIR__ . '/product-form.php'; ?>
        <button class="btn" type="submit">Save Product</button>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
