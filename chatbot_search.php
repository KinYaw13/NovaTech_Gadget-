<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/chatbot_request.php';

function chatbot_json(array $payload): never
{
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function chatbot_language(string $message): string
{
    if (preg_match('/\p{Han}/u', $message) && preg_match('/[a-z]/i', $message)) {
        return 'mixed';
    }
    return preg_match('/\p{Han}/u', $message) ? 'zh' : 'en';
}

function chatbot_text(string $key, string $language): string
{
    $texts = [
        'greeting' => [
            'en' => "Hi, I'm NovaTech AI Assistant. I can help you search gadgets in our store or submit unavailable gadget requests to admin for approval.",
            'zh' => '你好，我是 NovaTech AI Assistant。我可以帮你查找本站的电子产品，或者帮你把没有的商品提交给管理员审核。',
            'mixed' => '你好，我是 NovaTech AI Assistant. I can help you search NovaTech gadgets or submit unavailable products to admin.',
        ],
        'blocked' => [
            'en' => 'Sorry, I can only help with NovaTech gadget and technology product searches.',
            'zh' => '不好意思，我只能帮你查询 NovaTech Gadgets 的电子产品和科技配件。',
            'mixed' => '不好意思，我只能帮你查询 NovaTech Gadgets gadget / tech products.',
        ],
        'empty' => [
            'en' => 'Please enter a gadget product question.',
            'zh' => '请输入你想查询的电子产品。',
            'mixed' => '请输入 gadget product question.',
        ],
        'local_found' => [
            'en' => 'Yes, I found this product in NovaTech Gadgets.',
            'zh' => '有的，我在 NovaTech Gadgets 找到这个产品。',
            'mixed' => '有的，I found this product in NovaTech Gadgets.',
        ],
        'need_price' => [
            'en' => 'I could not find this product in our store. If you want, I can send a product request to admin for approval. Please enter your expected price. Only gadget requests RM300 or above can be submitted.',
            'zh' => '我在本站没有找到这个商品。如果你要的话，我可以帮你提交商品请求给管理员审核。请输入你预计的价格，RM300 或以上才可以提交。',
            'mixed' => '我在本站没有找到这个商品。You can submit a request to admin. Please enter expected price, RM300 or above.',
        ],
        'invalid_price' => [
            'en' => 'Please enter a valid price, for example RM899.',
            'zh' => '请输入正确的价格，例如 RM899。',
            'mixed' => '请输入正确价格，例如 RM899.',
        ],
        'price_rejected' => [
            'en' => 'Sorry, this product is currently not available in NovaTech Gadgets.',
            'zh' => '不好意思，这个商品目前 NovaTech Gadgets 没有提供。',
            'mixed' => '不好意思，这个商品目前 NovaTech Gadgets not available.',
        ],
        'request_sent' => [
            'en' => 'Your product request has been sent to admin for approval.',
            'zh' => '你的商品请求已经提交给管理员审核。',
            'mixed' => '你的商品请求已经 sent to admin for approval.',
        ],
        'error' => [
            'en' => 'Request could not be completed. Please try again.',
            'zh' => '请求暂时无法完成，请再试一次。',
            'mixed' => '请求暂时无法完成，请 try again.',
        ],
    ];

    return $texts[$key][$language] ?? $texts[$key]['en'];
}

function chatbot_normalize_query(string $message): string
{
    $normalized = mb_strtolower($message, 'UTF-8');
    $plainReplacements = [
        '/\bu\b/' => 'you',
        '/\bxiaomo\b/' => 'xiaomi',
        '/\bxiomi\b/' => 'xiaomi',
        '/\bxioami\b/' => 'xiaomi',
        '/\bipone\b/' => 'iphone',
        '/\bsamsong\b/' => 'samsung',
        '/\bnitendo\b/' => 'nintendo',
        '/\bnentendo\b/' => 'nintendo',
        '/\bairpod\b/' => 'airpods',
        '/\bair pods\b/' => 'airpods',
        '/\bmac book\b/' => 'macbook',
        '/\bps 5\b/' => 'ps5',
    ];
    foreach ($plainReplacements as $pattern => $replacement) {
        $normalized = (string) preg_replace($pattern, $replacement, $normalized);
    }

    $chinese = [
        '小米' => ' xiaomi ',
        '苹果' => ' apple iphone ',
        '三星' => ' samsung ',
        '华为' => ' huawei ',
        '荣耀' => ' honor ',
        '手机' => ' smartphone ',
        '平板' => ' tablet ',
        '电脑' => ' laptop ',
        '笔电' => ' laptop ',
        '笔记本电脑' => ' laptop ',
        '游戏机' => ' console ',
        '任天堂' => ' nintendo ',
        '耳机' => ' earbuds headphones ',
        '充电器' => ' charger ',
        '数据线' => ' cable ',
        '键盘' => ' keyboard ',
        '鼠标' => ' mouse ',
        '显示器' => ' monitor ',
        '手表' => ' smartwatch ',
        '智能手表' => ' smartwatch ',
        '相机' => ' camera ',
        '配件' => ' accessory ',
        '科技产品' => ' tech gadget ',
        '电子产品' => ' tech gadget ',
    ];
    $normalized = str_replace(array_keys($chinese), array_values($chinese), $normalized);
    return trim((string) preg_replace('/\s+/', ' ', $normalized));
}

function chatbot_allowed_topic(string $message): bool
{
    $keywords = [
        'phone', 'smartphone', 'iphone', 'samsung', 'xiaomi', 'redmi', 'poco', 'oppo', 'vivo', 'huawei', 'honor', 'realme', 'oneplus', 'pixel',
        'laptop', 'macbook', 'asus', 'lenovo', 'hp', 'dell', 'acer', 'msi', 'tablet', 'ipad',
        'nintendo', 'switch', 'steam deck', 'playstation', 'ps5', 'xbox', 'console',
        'smartwatch', 'watch', 'apple watch', 'galaxy watch', 'fitbit', 'airpods', 'earbuds', 'headphones', 'speaker',
        'charger', 'cable', 'adapter', 'power bank', 'keyboard', 'mouse', 'monitor', 'ssd', 'hard drive', 'usb', 'ram', 'cpu', 'gpu',
        'camera', 'gopro', 'gadget', 'tech', 'device', 'accessory', 'warranty', 'price', 'stock',
    ];
    $haystack = chatbot_normalize_query($message);
    foreach ($keywords as $keyword) {
        if (str_contains($haystack, $keyword)) {
            return true;
        }
    }
    return false;
}

function chatbot_search_terms(string $message): array
{
    $stopWords = ['do', 'you', 'u', 'have', 'find', 'me', 'i', 'want', 'sell', 'the', 'a', 'an', 'for', 'please', 'can', 'could', 'is', 'there', 'any'];
    $normalized = chatbot_normalize_query($message);
    $rawTerms = preg_split('/\s+/', preg_replace('/[^a-z0-9\s]+/i', ' ', strtolower($normalized)));
    $terms = [];
    foreach ($rawTerms as $term) {
        $term = trim((string) $term);
        if (strlen($term) < 2 || in_array($term, $stopWords, true)) {
            continue;
        }
        $terms[] = $term;
    }
    return array_values(array_unique(array_slice($terms, 0, 8)));
}

function chatbot_search_local(PDO $pdo, string $message): array
{
    $terms = chatbot_search_terms($message);
    if (!$terms) {
        return [];
    }

    $conditions = [];
    $params = [];
    foreach ($terms as $term) {
        $conditions[] = '(LOWER(p.product_name) LIKE ? OR LOWER(p.brand) LIKE ? OR LOWER(p.description) LIKE ? OR LOWER(c.category_name) LIKE ?)';
        $like = '%' . $term . '%';
        array_push($params, $like, $like, $like, $like);
    }

    $sql = 'SELECT p.product_id, p.product_name, p.brand, p.description, p.price, p.stock_quantity, p.image, p.rating, p.warranty_period, p.warranty_type, c.category_name
            FROM products p
            LEFT JOIN categories c ON c.category_id = p.category_id
            WHERE p.status = "active" AND (' . implode(' OR ', $conditions) . ')
            LIMIT 16';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    $genericTerms = ['phone', 'smartphone', 'laptop', 'tablet', 'ipad', 'watch', 'smartwatch', 'earbuds', 'headphones', 'keyboard', 'mouse', 'charger', 'cable', 'adapter', 'case', 'cover', 'console', 'gadget', 'tech', 'device', 'accessory', 'price', 'stock'];
    $specificTerms = array_values(array_filter($terms, static fn(string $term): bool => !in_array($term, $genericTerms, true)));
    if ($specificTerms) {
        $results = array_values(array_filter($results, static function (array $row) use ($specificTerms): bool {
            $haystack = strtolower((string) $row['product_name'] . ' ' . (string) $row['brand'] . ' ' . (string) ($row['description'] ?? ''));
            foreach ($specificTerms as $term) {
                if (!str_contains($haystack, $term)) {
                    return false;
                }
            }
            return true;
        }));
    }

    $scoreRow = static function (array $row) use ($terms): int {
        $name = strtolower((string) $row['product_name']);
        $brand = strtolower((string) $row['brand']);
        $category = strtolower((string) ($row['category_name'] ?? ''));
        $description = strtolower((string) ($row['description'] ?? ''));
        $score = 0;
        foreach ($terms as $term) {
            if ($brand === $term) $score += 90;
            if (str_contains($name, $term)) $score += 45;
            if (str_contains($brand, $term)) $score += 35;
            if (str_contains($description, $term)) $score += 18;
            if (str_contains($category, $term)) $score += 12;
        }
        return $score;
    };

    usort($results, static function (array $a, array $b) use ($scoreRow): int {
        $diff = $scoreRow($b) <=> $scoreRow($a);
        return $diff !== 0 ? $diff : strcmp((string) $a['product_name'], (string) $b['product_name']);
    });

    return array_values(array_filter(array_slice($results, 0, 3), static fn(array $row): bool => $scoreRow($row) > 0));
}

function chatbot_product_payload(array $product): array
{
    return [
        'id' => (int) $product['product_id'],
        'name' => $product['product_name'],
        'brand' => $product['brand'],
        'category' => $product['category_name'] ?? 'Uncategorized',
        'price' => money($product['price']),
        'stock' => (int) $product['stock_quantity'] > 0 ? 'Available' : 'Out of stock',
        'warranty' => trim((string) ($product['warranty_period'] ?? '1 Year') . ' ' . (string) ($product['warranty_type'] ?? 'Manufacturer Warranty')),
        'rating' => isset($product['rating']) ? number_format((float) $product['rating'], 1) . ' / 5' : '',
        'image' => $product['image'] ?? '',
        'view_url' => 'product-detail.php?id=' . (int) $product['product_id'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatbot_json(['ok' => false, 'message' => chatbot_text('empty', 'en')]);
}

$message = trim((string) ($_POST['message'] ?? ''));
$manualPriceRaw = trim((string) ($_POST['manual_price'] ?? ''));
$email = trim((string) ($_POST['email'] ?? (current_user()['email'] ?? '')));
$language = chatbot_language($message);
$normalizedQuery = chatbot_normalize_query($message);

if ($message === '') {
    chatbot_json(['ok' => false, 'message' => chatbot_text('empty', $language)]);
}

if (!chatbot_allowed_topic($message)) {
    chatbot_json([
        'ok' => true,
        'type' => 'blocked',
        'message' => chatbot_text('blocked', $language),
    ]);
}

$local = chatbot_search_local($pdo, $normalizedQuery);
if (!$local && $normalizedQuery !== $message) {
    $local = chatbot_search_local($pdo, $message);
}

if ($local) {
    chatbot_json([
        'ok' => true,
        'type' => 'local',
        'message' => chatbot_text('local_found', $language),
        'products' => array_map('chatbot_product_payload', $local),
    ]);
}

$productName = chatbot_infer_product_name($normalizedQuery !== '' ? $normalizedQuery : $message);
$category = chatbot_guess_category($normalizedQuery . ' ' . $message);
$brand = chatbot_guess_brand($productName . ' ' . $normalizedQuery);
$manualPrice = $manualPriceRaw !== '' ? chatbot_parse_price($manualPriceRaw) : null;

if ($manualPriceRaw !== '' && $manualPrice === null) {
    chatbot_json([
        'ok' => true,
        'type' => 'invalid_price',
        'message' => chatbot_text('invalid_price', $language),
        'pending_request' => [
            'product_name' => $productName,
            'normalized_query' => $normalizedQuery,
            'category' => $category,
            'brand' => $brand,
        ],
    ]);
}

if ($manualPrice === null) {
    chatbot_json([
        'ok' => true,
        'type' => 'need_price',
        'message' => chatbot_text('need_price', $language),
        'pending_request' => [
            'product_name' => $productName,
            'normalized_query' => $normalizedQuery,
            'category' => $category,
            'brand' => $brand,
            'customer_message' => $message,
        ],
    ]);
}

if ($manualPrice < 300) {
    chatbot_json([
        'ok' => true,
        'type' => 'price_rejected',
        'message' => chatbot_text('price_rejected', $language),
    ]);
}

try {
    $currentUser = current_user();
    $requestId = save_product_request($pdo, [
        'customer_id' => $currentUser && ($currentUser['role'] ?? '') === 'customer' ? (int) $currentUser['user_id'] : null,
        'requested_by_email' => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '',
        'customer_message' => $message,
        'normalized_query' => $normalizedQuery,
        'product_name' => $productName,
        'brand' => $brand,
        'category' => $category,
        'estimated_price' => $manualPrice,
        'description' => 'Customer expected price request submitted through NovaTech AI Assistant. Admin must approve before adding the product.',
    ]);
} catch (Throwable $exception) {
    chatbot_json(['ok' => false, 'type' => 'error', 'message' => chatbot_text('error', $language)]);
}

chatbot_json([
    'ok' => true,
    'type' => 'request_sent',
    'message' => chatbot_text('request_sent', $language),
    'request' => [
        'id' => $requestId,
        'product_name' => $productName,
        'brand' => $brand,
        'category' => $category,
        'estimated_price' => money($manualPrice),
        'status' => 'Pending Admin Approval',
    ],
]);
