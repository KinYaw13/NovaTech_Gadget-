<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

function chatbot_request_json(array $payload): never
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function chatbot_request_language(string $message): string
{
    return preg_match('/\p{Han}/u', $message) ? 'zh' : 'en';
}

function chatbot_request_text(string $key, string $language): string
{
    $texts = [
        'invalid_price' => [
            'en' => 'Please enter a valid price, for example RM899.',
            'zh' => '请输入正确的价格，例如 RM899。',
        ],
        'price_rejected' => [
            'en' => 'Sorry, this product is currently not available in NovaTech Gadgets.',
            'zh' => '不好意思，这个商品目前 NovaTech Gadgets 没有提供。',
        ],
        'request_sent' => [
            'en' => 'Your product request has been sent to admin for approval.',
            'zh' => '你的商品请求已经提交给管理员审核。',
        ],
        'save_failed' => [
            'en' => 'Request could not be saved. Please try again.',
            'zh' => '商品请求暂时无法保存，请再试一次。',
        ],
    ];
    return $texts[$key][$language] ?? $texts[$key]['en'];
}

function ensure_product_requests_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NULL,
        requested_by_email VARCHAR(160) NULL,
        customer_message TEXT NOT NULL,
        normalized_query VARCHAR(255) NULL,
        product_name VARCHAR(180) NOT NULL,
        brand VARCHAR(100) NULL,
        category VARCHAR(100) NULL,
        estimated_price DECIMAL(10,2) NOT NULL,
        product_image_url VARCHAR(700) NULL,
        source_url VARCHAR(700) NULL,
        description TEXT NULL,
        status ENUM('Pending','Approved','Rejected','Added') NOT NULL DEFAULT 'Pending',
        admin_note TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $columns = [
        'customer_id' => 'ALTER TABLE product_requests ADD COLUMN customer_id INT NULL AFTER id',
        'requested_by_email' => 'ALTER TABLE product_requests ADD COLUMN requested_by_email VARCHAR(160) NULL AFTER customer_id',
        'customer_message' => 'ALTER TABLE product_requests ADD COLUMN customer_message TEXT NULL AFTER requested_by_email',
        'normalized_query' => 'ALTER TABLE product_requests ADD COLUMN normalized_query VARCHAR(255) NULL AFTER customer_message',
        'brand' => 'ALTER TABLE product_requests ADD COLUMN brand VARCHAR(100) NULL AFTER product_name',
        'category' => 'ALTER TABLE product_requests ADD COLUMN category VARCHAR(100) NULL AFTER brand',
        'estimated_price' => 'ALTER TABLE product_requests ADD COLUMN estimated_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER category',
        'product_image_url' => 'ALTER TABLE product_requests ADD COLUMN product_image_url VARCHAR(700) NULL AFTER estimated_price',
        'source_url' => 'ALTER TABLE product_requests ADD COLUMN source_url VARCHAR(700) NULL AFTER product_image_url',
        'description' => 'ALTER TABLE product_requests ADD COLUMN description TEXT NULL AFTER source_url',
        'admin_note' => 'ALTER TABLE product_requests ADD COLUMN admin_note TEXT NULL AFTER status',
        'updated_at' => 'ALTER TABLE product_requests ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at',
    ];

    foreach ($columns as $column => $sql) {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
            $stmt->execute(['product_requests', $column]);
            if ((int) $stmt->fetchColumn() === 0) {
                $pdo->exec($sql);
            }
        } catch (Throwable $exception) {
            // Keep the chatbot usable even if a local demo database blocks a migration.
        }
    }

    try {
        $pdo->exec("ALTER TABLE product_requests MODIFY status ENUM('Pending','Approved','Rejected','Added') NOT NULL DEFAULT 'Pending'");
    } catch (Throwable $exception) {
        // Existing compatible status columns are accepted for the demo.
    }
}

function chatbot_parse_price(string $value): ?float
{
    if (!preg_match('/(?:RM|MYR)?\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/i', $value, $match)) {
        return null;
    }

    return (float) str_replace(',', '', $match[1]);
}

function chatbot_infer_product_name(string $message): string
{
    $clean = preg_replace('/\b(do you have|do u have|do u sell|do you sell|find me|i want|can you find|please find|looking for|is there|any|have)\b/i', ' ', $message);
    $clean = preg_replace('/[?？。！!,]+/u', ' ', (string) $clean);
    $clean = trim((string) preg_replace('/\s+/', ' ', (string) $clean));
    return $clean !== '' ? mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8') : 'Requested Gadget Product';
}

function chatbot_guess_category(string $message): string
{
    $map = [
        'Gaming Console' => ['switch', 'nintendo', 'playstation', 'ps5', 'xbox', 'console', 'steam deck', 'handheld', 'rog ally', 'legion go', '游戏机', '任天堂'],
        'Smartphones' => ['phone', 'iphone', 'samsung', 'xiaomi', 'redmi', 'poco', 'oppo', 'vivo', 'huawei', 'honor', 'realme', 'oneplus', 'pixel', 'smartphone', '手机', '小米', '苹果', '三星', '华为'],
        'Laptops' => ['laptop', 'macbook', 'asus', 'lenovo', 'hp', 'dell', 'acer', 'msi', 'framework', 'razer', '电脑', '笔电'],
        'Tablets' => ['tablet', 'ipad', 'matepad', 'pad', '平板'],
        'Smartwatches' => ['watch', 'smartwatch', 'apple watch', 'galaxy watch', 'fitbit', 'garmin', 'amazfit', '手表'],
        'Earbuds' => ['earbuds', 'airpods', 'buds', '耳机'],
        'Headphones' => ['headphones', 'headset', 'speaker'],
        'Chargers' => ['charger', 'adapter', 'cable', 'power bank', '充电器', '数据线'],
        'Keyboards' => ['keyboard', '键盘'],
        'Mice' => ['mouse', '鼠标'],
        'Accessories' => ['monitor', 'ssd', 'hard drive', 'usb', 'camera', 'gopro', 'gpu', 'cpu', 'ram', 'accessory', '配件'],
    ];
    $haystack = mb_strtolower($message, 'UTF-8');
    foreach ($map as $category => $keywords) {
        foreach ($keywords as $keyword) {
            if (str_contains($haystack, mb_strtolower($keyword, 'UTF-8'))) {
                return $category;
            }
        }
    }
    return 'Accessories';
}

function chatbot_guess_brand(string $productName): string
{
    $brands = ['Apple', 'Samsung', 'Xiaomi', 'Redmi', 'POCO', 'OPPO', 'vivo', 'Huawei', 'HONOR', 'Realme', 'OnePlus', 'Google Pixel', 'Sony', 'Logitech', 'Razer', 'ASUS', 'Dell', 'Acer', 'MSI', 'Nintendo', 'Microsoft', 'PlayStation', 'Lenovo', 'HP', 'Steam', 'Valve', 'GoPro', 'Framework', 'Garmin', 'Fitbit'];
    foreach ($brands as $brand) {
        if (stripos($productName, $brand) !== false) {
            return $brand;
        }
    }
    if (preg_match('/小米/u', $productName)) return 'Xiaomi';
    if (preg_match('/苹果/u', $productName)) return 'Apple';
    if (preg_match('/三星/u', $productName)) return 'Samsung';
    if (preg_match('/任天堂/u', $productName)) return 'Nintendo';
    if (stripos($productName, 'switch') !== false) return 'Nintendo';
    if (stripos($productName, 'steam deck') !== false) return 'Valve';
    return 'Unknown';
}

function save_product_request(PDO $pdo, array $payload): int
{
    ensure_product_requests_schema($pdo);

    $price = (float) ($payload['estimated_price'] ?? 0);
    if ($price < 300) {
        throw new InvalidArgumentException('Only gadget requests RM300 or above can be sent for admin approval.');
    }

    $email = trim((string) ($payload['requested_by_email'] ?? ''));
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email = '';
    }

    $customerId = isset($payload['customer_id']) && (int) $payload['customer_id'] > 0 ? (int) $payload['customer_id'] : null;
    $productName = trim((string) ($payload['product_name'] ?? ''));
    $message = trim((string) ($payload['customer_message'] ?? $productName));
    $normalized = trim((string) ($payload['normalized_query'] ?? $productName));
    $brand = trim((string) ($payload['brand'] ?? chatbot_guess_brand($productName)));
    $category = trim((string) ($payload['category'] ?? chatbot_guess_category($productName . ' ' . $message)));
    $description = trim((string) ($payload['description'] ?? 'Customer submitted this unavailable gadget request through NovaTech AI Assistant. Admin approval is required before adding it to products.'));

    if ($productName === '') {
        $productName = chatbot_infer_product_name($message);
    }

    $stmt = $pdo->prepare('INSERT INTO product_requests (customer_id, requested_by_email, customer_message, normalized_query, product_name, brand, category, estimated_price, product_image_url, source_url, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "Pending")');
    $stmt->execute([
        $customerId,
        $email !== '' ? $email : null,
        $message !== '' ? $message : $productName,
        $normalized !== '' ? $normalized : null,
        $productName,
        $brand !== '' ? $brand : null,
        $category !== '' ? $category : null,
        $price,
        trim((string) ($payload['product_image_url'] ?? '')) ?: null,
        trim((string) ($payload['source_url'] ?? '')) ?: null,
        $description !== '' ? $description : null,
    ]);

    return (int) $pdo->lastInsertId();
}

if (basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === 'chatbot_request.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $currentUser = current_user();
        $message = trim((string) ($_POST['message'] ?? $_POST['customer_message'] ?? ''));
        $productName = trim((string) ($_POST['product_name'] ?? ''));
        $normalized = trim((string) ($_POST['normalized_query'] ?? $message));
        $language = chatbot_request_language($message . ' ' . $productName);
        $price = chatbot_parse_price((string) ($_POST['expected_price'] ?? $_POST['manual_price'] ?? ''));

        if ($price === null) {
            chatbot_request_json(['ok' => false, 'message' => chatbot_request_text('invalid_price', $language)]);
        }
        if ($price < 300) {
            chatbot_request_json(['ok' => false, 'type' => 'price_rejected', 'message' => chatbot_request_text('price_rejected', $language)]);
        }

        $requestId = save_product_request($pdo, [
            'customer_id' => $currentUser && ($currentUser['role'] ?? '') === 'customer' ? (int) $currentUser['user_id'] : null,
            'requested_by_email' => trim((string) ($_POST['email'] ?? ($currentUser['email'] ?? ''))),
            'customer_message' => $message,
            'normalized_query' => $normalized,
            'product_name' => $productName !== '' ? $productName : chatbot_infer_product_name($normalized !== '' ? $normalized : $message),
            'brand' => trim((string) ($_POST['brand'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? '')),
            'estimated_price' => $price,
            'description' => trim((string) ($_POST['notes'] ?? $_POST['description'] ?? 'Customer submitted this unavailable gadget request through NovaTech AI Assistant.')),
        ]);

        chatbot_request_json([
            'ok' => true,
            'message' => chatbot_request_text('request_sent', $language),
            'request' => [
                'id' => $requestId,
                'product_name' => $productName !== '' ? $productName : chatbot_infer_product_name($message),
                'estimated_price' => money($price),
                'status' => 'Pending Admin Approval',
            ],
        ]);
    } catch (Throwable $exception) {
        chatbot_request_json(['ok' => false, 'message' => chatbot_request_text('save_failed', $language ?? 'en')]);
    }
}
