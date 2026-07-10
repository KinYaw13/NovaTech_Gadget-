<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_name = 'novatech_gadgets';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Please start MySQL and import database/novatech_gadgets.sql.');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function money(float|string $value): string
{
    return 'RM ' . number_format((float) $value, 2);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function current_role(): string
{
    return $_SESSION['user']['role'] ?? 'visitor';
}

function is_guest(): bool
{
    return current_role() === 'guest';
}

function is_customer(): bool
{
    return current_role() === 'customer';
}

function is_admin(): bool
{
    return current_role() === 'admin';
}

function redirect(string $path): never
{
    header("Location: {$path}");
    exit;
}

function cart_count(PDO $pdo): int
{
    if (!isset($_SESSION['user']) || current_role() === 'guest') {
        return 0;
    }

    $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE user_id = ?');
    $stmt->execute([$_SESSION['user']['user_id']]);
    return (int) $stmt->fetchColumn();
}

function product_visual(array|string $product): string
{
    $name = is_array($product) ? (string) $product['product_name'] : $product;
    $image = product_image_url($product);
    $label = e($name);

    if ($image !== '') {
        return '<img class="real-product-img" src="' . e($image) . '" alt="' . $label . '" loading="lazy" referrerpolicy="no-referrer" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'grid\';">'
            . '<div class="image-fallback-card"><strong>Real product photo unavailable</strong><small>' . $label . '</small></div>'
            . '<span class="product-fallback-label">' . $label . '</span>';
    }

    return '<div class="image-fallback-card always-visible"><strong>Real product photo unavailable</strong><small>' . $label . '</small></div>';
}

function product_variants(string $name, float $fallbackPrice): array
{
    $variants = [
        'Apple iPhone 17 Pro Max' => [
            ['256GB', 5999.00],
            ['512GB', 6999.00],
            ['1TB', 7999.00],
            ['2TB', 9999.00],
        ],
        'Apple iPhone 17 Pro' => [
            ['256GB', 5499.00],
            ['512GB', 6499.00],
            ['1TB', 7499.00],
        ],
        'Apple iPhone Air' => [
            ['256GB', 4999.00],
            ['512GB', 5999.00],
            ['1TB', 6999.00],
        ],
        'Apple iPhone 17' => [
            ['256GB', 3999.00],
            ['512GB', 4999.00],
        ],
        'Apple iPhone 16' => [
            ['128GB', 3499.00],
            ['256GB', 3999.00],
            ['512GB', 4999.00],
        ],
        'Apple iPhone 15 Pro Max' => [
            ['256GB', 4999.00],
            ['512GB', 5799.00],
            ['1TB', 6599.00],
        ],
        'Samsung Galaxy S25 Ultra' => [
            ['12GB + 256GB', 5999.00],
            ['12GB + 512GB', 6599.00],
            ['12GB + 1TB', 7799.00],
        ],
        'Samsung Galaxy S24 Ultra' => [
            ['12GB + 256GB', 4699.00],
            ['12GB + 512GB', 5299.00],
            ['12GB + 1TB', 6499.00],
        ],
        'Samsung Galaxy Z Fold7' => [
            ['12GB + 256GB', 7799.00],
            ['12GB + 512GB', 8399.00],
            ['16GB + 1TB', 9599.00],
        ],
        'Samsung Galaxy Z Flip7' => [
            ['12GB + 256GB', 4999.00],
            ['12GB + 512GB', 5599.00],
        ],
        'OPPO Find X8 Pro' => [
            ['16GB + 512GB', 4999.00],
        ],
        'OPPO Find X8' => [
            ['12GB + 256GB', 3699.00],
            ['16GB + 512GB', 4299.00],
        ],
        'vivo X300 Pro' => [
            ['12GB + 256GB', 4599.00],
            ['16GB + 512GB', 4999.00],
            ['16GB + 1TB', 5999.00],
        ],
        'vivo X200 Pro' => [
            ['16GB + 512GB', 4699.00],
        ],
        'Xiaomi 15 Ultra' => [
            ['16GB + 512GB', 5199.00],
            ['16GB + 1TB', 5999.00],
        ],
        'HONOR Magic7 Pro' => [
            ['12GB + 512GB', 4599.00],
        ],
        'Huawei Pura 80 Ultra' => [
            ['16GB + 512GB', 5999.00],
            ['16GB + 1TB', 6999.00],
        ],
        'Apple AirPods Pro 3' => [
            ['USB-C MagSafe Charging Case', 1099.00],
        ],
        'Apple AirPods 4 with ANC' => [
            ['USB-C Charging Case', 829.00],
        ],
        'Samsung Galaxy Buds3 Pro' => [
            ['Standard', 999.00],
        ],
        'Sony WF-1000XM5' => [
            ['Standard', 1099.00],
        ],
        'Xiaomi Buds 5 Pro' => [
            ['Standard', 699.00],
        ],
        'Beats Studio Buds +' => [
            ['Standard', 699.00],
        ],
        'Razer BlackWidow V4 Pro 75%' => [
            ['Orange Tactile Switches', 1399.00],
            ['Green Clicky Switches', 1399.00],
        ],
        'Razer Huntsman V3 Pro TKL' => [
            ['Analog Optical Switches', 799.00],
        ],
        'Logitech MX Mechanical' => [
            ['Tactile Quiet', 699.00],
            ['Linear', 699.00],
            ['Clicky', 699.00],
        ],
        'Keychron Q1 HE' => [
            ['Magnetic Switches', 999.00],
        ],
        'ASUS ROG Azoth' => [
            ['ROG NX Switches', 1199.00],
        ],
        'Apple MacBook Air 13 M4' => [
            ['16GB + 256GB', 4499.00],
            ['16GB + 512GB', 5499.00],
            ['24GB + 512GB', 6299.00],
        ],
        'Apple MacBook Air 15 M4' => [
            ['16GB + 256GB', 5499.00],
            ['16GB + 512GB', 6499.00],
            ['24GB + 512GB', 7299.00],
        ],
        'Apple MacBook Pro 14 M4' => [
            ['16GB + 512GB', 6999.00],
            ['24GB + 1TB', 8999.00],
        ],
        'Apple MacBook Pro 16 M4 Pro' => [
            ['24GB + 512GB', 10499.00],
            ['48GB + 1TB', 13999.00],
        ],
        'Dell XPS 13' => [
            ['16GB + 512GB', 5999.00],
            ['32GB + 1TB', 7499.00],
        ],
        'ASUS Zenbook 14 OLED' => [
            ['16GB + 1TB', 4299.00],
            ['32GB + 1TB', 5999.00],
        ],
        'Logitech MX Anywhere 3S' => [
            ['Standard', 399.00],
        ],
        'Razer Basilisk V3 Pro' => [
            ['Standard', 699.00],
        ],
        'Razer Viper V3 Pro' => [
            ['Standard', 799.00],
        ],
        'Apple Magic Mouse USB-C' => [
            ['Standard', 399.00],
        ],
        'Apple Watch Series 11' => [
            ['42mm GPS', 1799.00],
            ['46mm GPS', 1949.00],
            ['46mm GPS + Cellular', 2399.00],
        ],
        'Apple Watch SE 3' => [
            ['40mm GPS', 1049.00],
            ['44mm GPS', 1199.00],
            ['44mm GPS + Cellular', 1599.00],
        ],
        'Apple Watch Ultra 3' => [
            ['49mm GPS + Cellular', 3699.00],
        ],
        'Samsung Galaxy Watch8' => [
            ['40mm Bluetooth', 1299.00],
            ['44mm Bluetooth', 1399.00],
            ['44mm LTE', 1699.00],
        ],
        'Samsung Galaxy Watch Ultra' => [
            ['47mm LTE', 2799.00],
        ],
        'Xiaomi Watch S4' => [
            ['Standard', 699.00],
            ['Leather Strap Edition', 899.00],
        ],
        'Fitbit Charge 6' => [
            ['Standard', 799.00],
        ],
        'Apple iPad Air M3' => [
            ['11-inch 128GB Wi-Fi', 2799.00],
            ['11-inch 256GB Wi-Fi', 3299.00],
            ['13-inch 128GB Wi-Fi', 3699.00],
            ['13-inch 256GB Wi-Fi', 4199.00],
        ],
        'Apple iPad Pro M4' => [
            ['11-inch 256GB Wi-Fi', 4499.00],
            ['13-inch 256GB Wi-Fi', 5799.00],
            ['13-inch 512GB Wi-Fi', 6799.00],
        ],
        'Samsung Galaxy Tab S10 Ultra' => [
            ['12GB + 256GB', 5699.00],
            ['12GB + 512GB', 6299.00],
            ['16GB + 1TB', 7499.00],
        ],
        'Xiaomi Pad 7 Pro' => [
            ['8GB + 256GB', 1899.00],
            ['12GB + 512GB', 2399.00],
        ],
        'Huawei MatePad Pro 13.2' => [
            ['12GB + 256GB', 3999.00],
            ['12GB + 512GB', 4999.00],
        ],
    ];

    return $variants[$name] ?? [['Standard', $fallbackPrice]];
}

function product_variant_price(string $name, string $variant, float $fallbackPrice): float
{
    foreach (product_variants($name, $fallbackPrice) as $option) {
        if ($option[0] === $variant) {
            return (float) $option[1];
        }
    }

    return (float) product_variants($name, $fallbackPrice)[0][1];
}

function product_color_images(string $name, string $defaultImage): array
{
    $images = [
        'Apple iPhone 17 Pro Max' => [
            'Cosmic Orange' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-finish-select-202509-6-9inch-cosmicorange?wid=900&hei=900&fmt=png-alpha',
            'Deep Blue' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-finish-select-202509-6-9inch-deepblue?wid=900&hei=900&fmt=png-alpha',
            'Silver' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-finish-select-202509-6-9inch-silver?wid=900&hei=900&fmt=png-alpha',
        ],
        'Apple iPhone 17 Pro' => [
            'Cosmic Orange' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-finish-select-202509-6-3inch-cosmicorange?wid=900&hei=900&fmt=png-alpha',
            'Deep Blue' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-finish-select-202509-6-3inch-deepblue?wid=900&hei=900&fmt=png-alpha',
            'Silver' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-finish-select-202509-6-3inch-silver?wid=900&hei=900&fmt=png-alpha',
        ],
        'Apple iPhone Air' => [
            'Sky Blue' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-air-finish-select-202509-skyblue?wid=900&hei=900&fmt=png-alpha',
            'Light Gold' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-air-finish-select-202509-lightgold?wid=900&hei=900&fmt=png-alpha',
            'Cloud White' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-air-finish-select-202509-cloudwhite?wid=900&hei=900&fmt=png-alpha',
            'Space Black' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-air-finish-select-202509-spaceblack?wid=900&hei=900&fmt=png-alpha',
        ],
        'Apple iPhone 17' => [
            'Lavender' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-finish-select-202509-lavender?wid=900&hei=900&fmt=png-alpha',
            'Sage' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-finish-select-202509-sage?wid=900&hei=900&fmt=png-alpha',
            'Mist Blue' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-finish-select-202509-mistblue?wid=900&hei=900&fmt=png-alpha',
            'White' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-finish-select-202509-white?wid=900&hei=900&fmt=png-alpha',
            'Black' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-finish-select-202509-black?wid=900&hei=900&fmt=png-alpha',
        ],
        'Samsung Galaxy S25 Ultra' => [
            'Titanium Silverblue' => 'https://images.samsung.com/rs/smartphones/galaxy-s25-ultra/buy/10_WITB/10-1_Basic-Color/WITB-Titanium-Silverblue_PC.jpg',
            'Titanium Black' => 'https://images.samsung.com/rs/smartphones/galaxy-s25-ultra/buy/10_WITB/10-1_Basic-Color/WITB_Titanium-Black_PC.jpg',
            'Titanium Gray' => 'https://images.samsung.com/rs/smartphones/galaxy-s25-ultra/buy/10_WITB/10-1_Basic-Color/WITB_Titanium-Gray_PC.jpg',
            'Titanium Whitesilver' => 'https://images.samsung.com/rs/smartphones/galaxy-s25-ultra/buy/10_WITB/10-1_Basic-Color/WITB_Titanium-Whitesilver_PC.jpg',
            'Titanium Jetblack' => 'https://images.samsung.com/rs/smartphones/galaxy-s25-ultra/buy/10_WITB/10-2_Exclusive-Color/WITB_Titanium-Jetblack_PC.jpg',
        ],
        'Samsung Galaxy S24 Ultra' => [
            'Titanium Black' => 'https://images.samsung.com/is/image/samsung/assets/levant/smartphones/galaxy-s24-ultra/buy/S24Ultra-Color-Titanium_Black_PC.jpg',
            'Titanium Gray' => 'https://images.samsung.com/is/image/samsung/assets/levant/smartphones/galaxy-s24-ultra/buy/S24Ultra-Color-Titanium_Grey_PC.jpg',
            'Titanium Violet' => 'https://images.samsung.com/is/image/samsung/assets/levant/smartphones/galaxy-s24-ultra/buy/S24Ultra-Color-Titanium_Violet_PC.jpg',
            'Titanium Yellow' => 'https://images.samsung.com/is/image/samsung/assets/levant/smartphones/galaxy-s24-ultra/buy/S24Ultra-Color-Titanium_Yellow_PC.jpg',
        ],
        'Samsung Galaxy Z Fold7' => [
            'Blue Shadow' => 'https://images.samsung.com/is/image/samsung/assets/cl/smartphones/galaxy-z-fold7/buy/Q7_Color_Selection_Blue_Shadow_PC_1600x864.png',
            'Jetblack' => 'https://images.samsung.com/is/image/samsung/assets/cl/smartphones/galaxy-z-fold7/buy/Q7_Color_Selection_Jetblack_PC_1600x864.png',
            'Silver Shadow' => 'https://images.samsung.com/is/image/samsung/assets/cl/smartphones/galaxy-z-fold7/buy/Q7_Color_Selection_Silver_Shadow_PC_1600x864.png',
        ],
        'Samsung Galaxy Z Flip7' => [
            'Blue Shadow' => 'https://images.samsung.com/is/image/samsung/assets/cl/smartphones/galaxy-z-flip7/buy/B7_Color_Selection_Blue_Shadow_PC_1600x864.png',
            'Jetblack' => 'https://images.samsung.com/is/image/samsung/assets/cl/smartphones/galaxy-z-flip7/buy/product_color_jetBlack_PC.png',
            'Mint' => 'https://images.samsung.com/is/image/samsung/assets/cl/smartphones/galaxy-z-flip7/buy/B7_Color_Selection_Mint_PC_1600x864.png',
        ],
        'Apple iPhone 16' => [
            'Ultramarine' => 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-16-1.jpg',
        ],
        'Apple iPhone 15 Pro Max' => [
            'Natural Titanium' => 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-15-pro-max-1.jpg',
        ],
        'OPPO Find X8 Pro' => [
            'Pearl White' => 'https://www.oppo.com/content/dam/oppo/common/mkt/v2-2/find-x8-series-en/find-x8-pro/listpage/432-600-white.png',
        ],
        'OPPO Find X8' => [
            'Star Grey' => 'https://www.oppo.com/content/dam/oppo/common/mkt/v2-2/find-x8-series-en/find-x8/listpage/436-600-white-v2.png',
        ],
        'Xiaomi 15 Ultra' => [
            'Black' => 'https://i02.appmifile.com/mi-com-product/fly-birds/xiaomi-15-ultra/pc/black-active.png',
            'White' => 'https://i02.appmifile.com/mi-com-product/fly-birds/xiaomi-15-ultra/pc/white-active.png',
            'Silver Chrome' => 'https://i02.appmifile.com/mi-com-product/fly-birds/xiaomi-15-ultra/pc/SilverChrome-active.png',
        ],
        'Huawei Pura 80 Ultra' => [
            'Gold' => 'https://consumer.huawei.com/dam/content/dam/huawei-cbg-site/common/mkt/pdp/phones/pura80-ultra/list-gold.png',
        ],
        'Apple AirPods Pro 3' => [
            'White' => 'https://www.apple.com/v/airpods-pro/r/images/meta/og__c0ceegchesom_overview.png?202604261906',
        ],
        'Apple AirPods 4 with ANC' => [
            'White' => 'https://www.apple.com/v/airpods-4/g/images/meta/airpods-4__gnjh1t3yjxm6_og.png?202604230007',
        ],
        'Samsung Galaxy Buds3 Pro' => [
            'Silver' => 'https://i5.walmartimages.com/asr/a0a7118b-71be-4fa3-98c4-ef8f98f32dbd.b5530453763509be966b3012c81d1fcd.jpeg?odnHeight=612&odnWidth=612&odnBg=FFFFFF',
            'White' => 'https://i5.walmartimages.com/asr/16821107-b10b-4ccf-b929-969595913f3d.6cdc94cab0c88ed7ac906025525daebf.jpeg?odnHeight=612&odnWidth=612&odnBg=FFFFFF',
        ],
        'Xiaomi Buds 5 Pro' => [
            'Black' => 'https://i02.appmifile.com/455_item_my/25/02/2025/2e013f5e02abbb4487af8fc7aa5868b3.png',
        ],
        'Beats Studio Buds +' => [
            'Transparent' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/MQLK3?wid=1200&hei=630&fmt=jpeg&qlt=95',
        ],
        'Razer BlackWidow V4 Pro 75%' => [
            'Black' => 'https://assets2.razerzone.com/images/pnx.assets/3b09f11f56c96cab9def3c2825f00567/razer-blackwidow-v4-pro-75-og-image-1200x630-v2.webp',
        ],
        'Razer Huntsman V3 Pro TKL' => [
            'Black' => 'https://assets2.razerzone.com/images/pnx.assets/ce8efb94452a0b8f9d2e8dcebc9bab5c/razer-huntsman-v3-pro-tkl-ogimage-1200x630-v2.webp',
        ],
        'Keychron Q1 HE' => [
            'Black' => 'https://www.keychron.com/cdn/shop/files/Q1-HE-Iconic-Features.jpg?crop=center&height=1200&v=1754623218&width=1200',
        ],
        'ASUS ROG Azoth' => [
            'Black' => 'https://dlcdnwebimgs.asus.com/gain/145896AC-B462-4466-A1FE-935F085741F3',
        ],
        'Apple MacBook Air 13 M4' => [
            'Sky Blue' => 'https://www.apple.com/v/macbook-air/z/images/meta/macbook_air_mx__ez5y0k5yy7au_og.png?202605071756',
        ],
        'Apple MacBook Air 15 M4' => [
            'Midnight' => 'https://www.apple.com/v/macbook-air/z/images/meta/macbook_air_mx__ez5y0k5yy7au_og.png?202605071756',
        ],
        'Apple MacBook Pro 14 M4' => [
            'Space Black' => 'https://www.apple.com/v/macbook-pro/ax/images/meta/macbook-pro__difvbgz1plsi_og.png?202605071756',
        ],
        'Apple MacBook Pro 16 M4 Pro' => [
            'Silver' => 'https://www.apple.com/v/macbook-pro/ax/images/meta/macbook-pro__difvbgz1plsi_og.png?202605071756',
        ],
        'ASUS Zenbook 14 OLED' => [
            'Ponder Blue' => 'https://dlcdnwebimgs.asus.com/gain/282fa6b1-5d9e-4950-ab46-1da2defbe6a3/',
        ],
        'Logitech MX Master 3S' => [
            'Graphite' => 'https://resource.logitech.com/w_800,c_limit,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/mice/mx-master-3s/gallery/mx-master-3s-mouse-top-view-graphite.png',
        ],
        'Logitech MX Anywhere 3S' => [
            'Graphite' => 'https://resource.logitech.com/w_544%2Ch_466%2Car_7%3A6%2Cc_pad%2Cq_auto%2Cf_auto%2Cdpr_1.0/d_transparent.gif/content/dam/logitech/en/products/mice/mx-anywhere-3s/product-gallery/graphite/mx-anywhere-3s-mouse-top-view-graphite.png',
        ],
        'Logitech MX Mechanical' => [
            'Graphite' => 'https://resource.logitech.com/w_544%2Ch_466%2Car_7%3A6%2Cc_pad%2Cq_auto%2Cf_auto%2Cdpr_1.0/d_transparent.gif/content/dam/logitech/en/products/keyboards/mx-mechanical/migration-assets-for-delorean-2025/gallery/mx-mechanical-top-view-graphite-us.png',
        ],
        'Razer Basilisk V3 Pro' => [
            'Black' => 'https://assets2.razerzone.com/images/og-image/razer-basilisk-v3-pro-og-image.jpg',
        ],
        'Razer Viper V3 Pro' => [
            'Black' => 'https://assets2.razerzone.com/images/pnx.assets/24970f67be4ba9644e28720377d91cfb/razer-viper-v3-pro-1200x630.webp',
        ],
        'Apple Magic Mouse USB-C' => [
            'White' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/MXK53?wid=1200&hei=630&fmt=jpeg&qlt=95',
        ],
        'Xiaomi Watch S4' => [
            'Black' => 'https://i02.appmifile.com/mi-com-product/fly-birds/xiaomi-watch-s4/pc/8795cc657393233c682ee58d00be839c.png',
            'Silver' => 'https://i02.appmifile.com/mi-com-product/fly-birds/xiaomi-watch-s4/pc/8d5e52e06921d3d96fe06166c5154ec5.png',
        ],
        'Samsung Galaxy Watch8' => [
            'Graphite' => 'https://image-us.samsung.com/us/watches/galaxy-watch8/images/kv/11_Watch8-Graphite-44mm-Thumbnail-800x600.jpg',
            'Silver' => 'https://image-us.samsung.com/us/watches/galaxy-watch8/images/kv/11_Watch8-Silver-44mm-Thumbnail-800x600.jpg',
        ],
        'Samsung Galaxy Watch Ultra' => [
            'Titanium Gray' => 'https://images.samsung.com/is/image/samsung/p6pim/us/f2507/gallery/us-galaxy-watch-ultra-2025-l705-sm-l705uza1xaa-547907800?$PD_GALLERY_PNG$',
            'Titanium Silver' => 'https://images.samsung.com/is/image/samsung/p6pim/us/f2507/gallery/us-galaxy-watch-ultra-2025-l705-sm-l705uaw1xaa-547907779?$PD_GALLERY_PNG$',
            'Titanium White' => 'https://images.samsung.com/is/image/samsung/p6pim/us/f2507/gallery/us-galaxy-watch-ultra-2025-l705-556192-sm-l705uaw4xaa-547978666?$PD_GALLERY_PNG$',
        ],
        'Apple Watch Series 11' => [
            'Rose Gold' => 'https://www.apple.com/newsroom/images/2025/09/apple-debuts-apple-watch-series-11-featuring-groundbreaking-health-insights/article/Apple-Watch-Series-11-aluminum-rose-gold-250909_inline.jpg.large.jpg',
            'Silver' => 'https://www.apple.com/newsroom/images/2025/09/apple-debuts-apple-watch-series-11-featuring-groundbreaking-health-insights/article/Apple-Watch-Series-11-aluminum-silver-250909_inline.jpg.large.jpg',
            'Jet Black' => 'https://www.apple.com/newsroom/images/2025/09/apple-debuts-apple-watch-series-11-featuring-groundbreaking-health-insights/article/Apple-Watch-Series-11-aluminum-jet-black-250909_inline.jpg.large.jpg',
        ],
        'Apple Watch SE 3' => [
            'Midnight' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/watch-compare-se-202509?wid=1000&hei=1000&fmt=jpeg&qlt=90',
            'Starlight' => 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/se-case-unselect-gallery-1-202509?wid=1200&hei=630&fmt=jpeg&qlt=95',
        ],
        'Apple Watch Ultra 3' => [
            'Natural Titanium' => 'https://www.apple.com/newsroom/images/2025/09/introducing-apple-watch-ultra-3/article/Apple-Watch-Ultra-3-hero-250909_big.jpg.large.jpg',
            'Black Titanium' => 'https://www.apple.com/newsroom/images/2025/09/introducing-apple-watch-ultra-3/article/Apple-Watch-Ultra-3-bands-250909_big.jpg.large.jpg',
        ],
        'Fitbit Charge 6' => [
            'Black' => 'https://lh3.googleusercontent.com/AUf6sQgatEhcTKNlETH2N4ba6YgqRnskBUpBADS-pDhRP9pSZVvPOGXXnCo-8VjUBLYoXnOoEJFKhziMTQQuwotrc96Jls5p5-I=s1200-w1200-e365-rw-v0-nu',
            'Champagne Gold' => 'https://lh3.googleusercontent.com/90ZKOoZUe6VhxuxeUIV2ATGwxeoC35rrdCqC-mfvfYW5tpSqvktKqHmszvF2Wg3QNiHTZ0ID7g8YedEOTKfzwhTjTTrzwLn_2DRN=s1200-w1200-e365-rw-v0-nu',
            'Silver' => 'https://lh3.googleusercontent.com/55bOjDj_y8mm3MI8kv7FOT9J-pPkaveVrrMs2ADGkAi6SoOff4oilDuO5nq2zDOvNdZoC3OC5rGp7YzBjycHo92iJx5IdD88acnL=s1200-w1200-e365-rw-v0-nu',
        ],
        'Apple iPad Air M3' => [
            'Blue' => 'https://www.apple.com/v/ipad-air/ah/images/meta/ipad-air_overview__bc2fd15uec0y_og.png?202606081814',
        ],
        'Apple iPad Pro M4' => [
            'Space Black' => 'https://www.apple.com/v/ipad-pro/aw/images/meta/ipad-pro_overview__bu4cql27diaa_og.png?202605071756',
        ],
        'Xiaomi Pad 7 Pro' => [
            'Blue' => 'https://i02.appmifile.com/mi-com-product/fly-birds/xiaomi-pad-7-pro/pc/5484effe86e2b64e1bf9d0235e7bd126.jpg',
        ],
        'Apple Clear Case with MagSafe - iPhone 17 Series' => [
            'Clear' => 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MA7E4?wid=1144&hei=1144&fmt=jpeg&qlt=90',
        ],
        'Apple Clear Case with MagSafe - iPhone 16 Series' => [
            'Clear' => 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MA7E4?wid=1144&hei=1144&fmt=jpeg&qlt=90',
        ],
    ];

    return $images[$name] ?? [];
}

function product_uploaded_color_rows(PDO $pdo, int $productId): array
{
    if ($productId <= 0) {
        return [];
    }

    try {
        $stmt = $pdo->prepare('SELECT color_name, color_hex, image FROM product_color_images WHERE product_id = ? ORDER BY color_id');
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    } catch (PDOException) {
        return [];
    }
}

function product_image_url(array|string $product): string
{
    $name = is_array($product) ? (string) $product['product_name'] : (string) $product;
    $stored = is_array($product) ? (string) ($product['image'] ?? '') : '';

    if ($stored !== '') {
        return $stored;
    }

    $images = [
        'Apple iPhone 15 Pro Max' => 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-15-pro-max-1.jpg',
        'Samsung Galaxy S24 Ultra' => 'https://images.samsung.com/co/smartphones/galaxy-s24-ultra/images/galaxy-s24-ultra-highlights-kv.jpg?imbypass=true',
        'Huawei Pura 70 Ultra' => 'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-pura70-ultra-1.jpg',
        'vivo X100 Pro' => 'https://fdn2.gsmarena.com/vv/pics/vivo/vivo-x100-pro-1.jpg',
        'OPPO Find X7 Ultra' => 'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-find-x7-ultra-1.jpg',
        'Apple iPad Pro 13 2024' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/75/IPad_Pro_13-inch_backside.jpg/250px-IPad_Pro_13-inch_backside.jpg',
        'Samsung Galaxy Tab S9 Ultra' => 'https://images.samsung.com/ps/galaxy-tab-s9/feature/galaxy-tab-s9-ultra-graphite.jpg',
        'Huawei MatePad Pro 13.2' => 'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-matepad-pro-13-2-2023-1.jpg',
        'Dell XPS 13' => 'https://upload.wikimedia.org/wikipedia/commons/1/18/Dell_XPS_13_9360.jpg',
        'ASUS Zenbook 14 OLED' => 'https://dlcdnwebimgs.asus.com/gain/1B9B2F88-04EE-4A40-BD1B-4025FD7E7368',
        'Sony WH-1000XM5' => 'https://www.sony.com.my/image/5d02da5df552836db894cead8a68f5f4?fmt=png-alpha&wid=720',
        'Logitech MX Master 3S' => 'https://resource.logitech.com/w_800,c_limit,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/mice/mx-master-3s/gallery/mx-master-3s-mouse-top-view-graphite.png',
        'Logitech MX Anywhere 3S' => 'https://resource.logitech.com/w_544%2Ch_466%2Car_7%3A6%2Cc_pad%2Cq_auto%2Cf_auto%2Cdpr_1.0/d_transparent.gif/content/dam/logitech/en/products/mice/mx-anywhere-3s/product-gallery/graphite/mx-anywhere-3s-mouse-top-view-graphite.png',
        'Logitech MX Mechanical' => 'https://resource.logitech.com/w_544%2Ch_466%2Car_7%3A6%2Cc_pad%2Cq_auto%2Cf_auto%2Cdpr_1.0/d_transparent.gif/content/dam/logitech/en/products/keyboards/mx-mechanical/migration-assets-for-delorean-2025/gallery/mx-mechanical-top-view-graphite-us.png',
    ];

    if (isset($images[$name])) {
        return $images[$name];
    }

    return '';
}

function product_colors(string $name): array
{
    $sets = [
        'Apple iPhone 15 Pro Max' => [
            ['Natural Titanium', '#c9c2b8'],
        ],
        'Apple iPhone 16' => [
            ['Ultramarine', '#6376d8'],
        ],
        'Samsung Galaxy S24 Ultra' => [
            ['Titanium Black', '#343434'],
            ['Titanium Gray', '#b7b4aa'],
            ['Titanium Violet', '#4d4568'],
            ['Titanium Yellow', '#eadc9f'],
        ],
        'Huawei Pura 70 Ultra' => [
            ['Black', '#151515'],
            ['White', '#f6f4ef'],
            ['Brown', '#8c6b54'],
            ['Green', '#4e6b5d'],
        ],
        'vivo X100 Pro' => [
            ['Startrail Blue', '#31506f'],
            ['Asteroid Black', '#1c1d20'],
            ['Sunset Orange', '#d08356'],
        ],
        'OPPO Find X7 Ultra' => [
            ['Ocean Blue', '#315a83'],
            ['Desert Silver Moon', '#d8d2c3'],
            ['Pine Shadow', '#3e5147'],
        ],
        'Apple iPad Pro 13 2024' => [
            ['Silver', '#dedede'],
            ['Space Black', '#303034'],
        ],
        'Samsung Galaxy Tab S9 Ultra' => [
            ['Graphite', '#4a4a4a'],
            ['Beige', '#d8caba'],
        ],
        'Huawei MatePad Pro 13.2' => [
            ['Golden Black', '#202020'],
            ['Green', '#506c5e'],
            ['White', '#f4f2ed'],
        ],
        'Dell XPS 13' => [
            ['Platinum', '#d8d8d8'],
            ['Graphite', '#444444'],
        ],
        'ASUS Zenbook 14 OLED' => [
            ['Ponder Blue', '#26364c'],
            ['Foggy Silver', '#cfd2d4'],
        ],
        'Sony WH-1000XM5' => [
            ['Black', '#171717'],
            ['Silver', '#d2cec5'],
            ['Midnight Blue', '#222f4d'],
        ],
        'Logitech MX Master 3S' => [
            ['Graphite', '#3f4145'],
            ['Pale Gray', '#d8d8d4'],
        ],
        'Apple iPhone 17 Pro Max' => [
            ['Cosmic Orange', '#c56a2d'],
            ['Deep Blue', '#1c314c'],
            ['Silver', '#d8d8d1'],
        ],
        'Apple iPhone 17 Pro' => [
            ['Cosmic Orange', '#c56a2d'],
            ['Deep Blue', '#1c314c'],
            ['Silver', '#d8d8d1'],
        ],
        'Apple iPhone Air' => [
            ['Sky Blue', '#b9d7e9'],
            ['Light Gold', '#dfc88f'],
            ['Cloud White', '#f4f4ef'],
            ['Space Black', '#1d1d1f'],
        ],
        'Apple iPhone 17' => [
            ['Lavender', '#cdb8e8'],
            ['Sage', '#aeb9a2'],
            ['Mist Blue', '#a9c4dc'],
            ['White', '#f4f4ef'],
            ['Black', '#1d1d1f'],
        ],
        'Samsung Galaxy S25 Ultra' => [
            ['Titanium Silverblue', '#aeb8ca'],
            ['Titanium Black', '#202124'],
            ['Titanium Gray', '#9c968c'],
            ['Titanium Whitesilver', '#e2e1dc'],
            ['Titanium Jetblack', '#111111'],
        ],
        'Samsung Galaxy Z Fold7' => [
            ['Blue Shadow', '#263f73'],
            ['Jetblack', '#151515'],
            ['Silver Shadow', '#c8c9c9'],
        ],
        'Samsung Galaxy Z Flip7' => [
            ['Blue Shadow', '#263f73'],
            ['Jetblack', '#151515'],
            ['Mint', '#a9c8bd'],
        ],
        'OPPO Find X8 Pro' => [
            ['Pearl White', '#f4f1e8'],
        ],
        'OPPO Find X8' => [
            ['Star Grey', '#8f9292'],
        ],
        'vivo X300 Pro' => [
            ['Phantom Black', '#151515'],
            ['Mist Blue', '#8faec9'],
            ['Dune Brown', '#8b6c52'],
            ['Cloud White', '#f4f4ef'],
        ],
        'Xiaomi 15 Ultra' => [
            ['Black', '#151515'],
            ['White', '#f4f4ef'],
            ['Silver Chrome', '#bfc3c8'],
        ],
        'HONOR Magic7 Pro' => [
            ['Lunar Shadow Grey', '#8d8d89'],
            ['Breeze Blue', '#9ab7ce'],
            ['Black', '#151515'],
        ],
        'Huawei Pura 80 Ultra' => [
            ['Gold', '#c8aa70'],
            ['Black', '#151515'],
            ['White', '#f4f4ef'],
        ],
        'Apple AirPods Pro 3' => [
            ['White', '#f6f6f2'],
        ],
        'Apple AirPods 4 with ANC' => [
            ['White', '#f6f6f2'],
        ],
        'Samsung Galaxy Buds3 Pro' => [
            ['Silver', '#d8d8d8'],
            ['White', '#f4f4ef'],
        ],
        'Sony WF-1000XM5' => [
            ['Black', '#171717'],
            ['Silver', '#d2cec5'],
        ],
        'Xiaomi Buds 5 Pro' => [
            ['Black', '#151515'],
            ['White', '#f4f4ef'],
            ['Gold', '#d6bd82'],
        ],
        'Beats Studio Buds +' => [
            ['Transparent', '#d9dde4'],
            ['Black', '#151515'],
            ['Ivory', '#f4f0e8'],
        ],
        'Razer BlackWidow V4 Pro 75%' => [
            ['Black', '#151515'],
        ],
        'Razer Huntsman V3 Pro TKL' => [
            ['Black', '#151515'],
        ],
        'Logitech MX Mechanical' => [
            ['Graphite', '#3f4145'],
            ['Pale Grey', '#d8d8d4'],
        ],
        'Keychron Q1 HE' => [
            ['Black', '#151515'],
            ['White', '#f4f4ef'],
        ],
        'ASUS ROG Azoth' => [
            ['Black', '#151515'],
            ['White', '#f4f4ef'],
        ],
        'Apple MacBook Air 13 M4' => [
            ['Sky Blue', '#a9cce4'],
            ['Silver', '#d8d8d8'],
            ['Starlight', '#eadcc8'],
            ['Midnight', '#202734'],
        ],
        'Apple MacBook Air 15 M4' => [
            ['Sky Blue', '#a9cce4'],
            ['Silver', '#d8d8d8'],
            ['Starlight', '#eadcc8'],
            ['Midnight', '#202734'],
        ],
        'Apple MacBook Pro 14 M4' => [
            ['Space Black', '#303034'],
            ['Silver', '#d8d8d8'],
        ],
        'Apple MacBook Pro 16 M4 Pro' => [
            ['Space Black', '#303034'],
            ['Silver', '#d8d8d8'],
        ],
        'Logitech MX Anywhere 3S' => [
            ['Graphite', '#3f4145'],
            ['Pale Grey', '#d8d8d4'],
            ['Rose', '#d7a9a7'],
        ],
        'Razer Basilisk V3 Pro' => [
            ['Black', '#151515'],
            ['White', '#f4f4ef'],
        ],
        'Razer Viper V3 Pro' => [
            ['Black', '#151515'],
            ['White', '#f4f4ef'],
        ],
        'Apple Magic Mouse USB-C' => [
            ['White', '#f4f4ef'],
            ['Black', '#151515'],
        ],
        'Apple Watch Series 11' => [
            ['Rose Gold', '#d8b59f'],
            ['Silver', '#d8d8d8'],
            ['Jet Black', '#151515'],
        ],
        'Apple Watch SE 3' => [
            ['Midnight', '#202734'],
            ['Starlight', '#eadcc8'],
        ],
        'Apple Watch Ultra 3' => [
            ['Natural Titanium', '#c9c2b8'],
            ['Black Titanium', '#151515'],
        ],
        'Samsung Galaxy Watch8' => [
            ['Graphite', '#3f4145'],
            ['Silver', '#d8d8d8'],
        ],
        'Samsung Galaxy Watch Ultra' => [
            ['Titanium Gray', '#9c968c'],
            ['Titanium Silver', '#d8d8d8'],
            ['Titanium White', '#f4f4ef'],
        ],
        'Xiaomi Watch S4' => [
            ['Black', '#151515'],
            ['Silver', '#d8d8d8'],
        ],
        'Fitbit Charge 6' => [
            ['Black', '#151515'],
            ['Champagne Gold', '#d7bd8c'],
            ['Silver', '#d8d8d8'],
        ],
        'Apple iPad Air M3' => [
            ['Space Grey', '#6e6e73'],
            ['Blue', '#a9cce4'],
            ['Purple', '#c7b7dd'],
            ['Starlight', '#eadcc8'],
        ],
        'Apple iPad Pro M4' => [
            ['Silver', '#d8d8d8'],
            ['Space Black', '#303034'],
        ],
        'Samsung Galaxy Tab S10 Ultra' => [
            ['Moonstone Grey', '#757575'],
            ['Platinum Silver', '#d8d8d8'],
        ],
        'Xiaomi Pad 7 Pro' => [
            ['Blue', '#9fbcd7'],
            ['Grey', '#9b9b9b'],
            ['Green', '#a7b7a4'],
        ],
        'Huawei MatePad Pro 13.2' => [
            ['Black', '#151515'],
            ['White', '#f4f4ef'],
            ['Green', '#506c5e'],
        ],
    ];

    $verifiedImages = product_color_images($name, '');
    if ($verifiedImages !== []) {
        $swatches = [];
        foreach ($sets[$name] ?? [] as $color) {
            $swatches[$color[0]] = $color[1];
        }

        return array_map(
            fn($colorName) => [$colorName, $swatches[$colorName] ?? '#d9dde4'],
            array_keys($verifiedImages)
        );
    }

    $hideUnverifiedColors = [
        'vivo X300 Pro',
        'vivo X200 Pro',
        'HONOR Magic7 Pro',
    ];

    if (in_array($name, $hideUnverifiedColors, true)) {
        return [];
    }

    if (str_contains($name, 'Case') || str_contains($name, 'Cover')) {
        if (str_contains($name, 'Clear')) {
            return [['Clear', '#f7fbff']];
        }

        return [];
    }

    if (str_contains($name, 'Cable') || str_contains($name, 'Adapter') || str_contains($name, 'Charger')) {
        return [];
    }

    return $sets[$name] ?? [];
}

function product_specs(string $name): array
{
    $specs = [
        'Apple iPhone 15 Pro Max' => ['6.7-inch Super Retina XDR display', 'A17 Pro chip', 'USB-C connector', 'Pro camera system'],
        'Samsung Galaxy S24 Ultra' => ['6.8-inch Dynamic AMOLED 2X', 'S Pen support', 'Snapdragon 8 Gen 3', '200MP wide camera'],
        'Huawei Pura 70 Ultra' => ['Large OLED display', 'XMAGE camera system', 'Fast wired charging', 'Premium camera design'],
        'vivo X100 Pro' => ['6.78-inch AMOLED display', 'ZEISS imaging system', 'Flagship chipset', 'Large battery'],
        'OPPO Find X7 Ultra' => ['Premium AMOLED display', 'Dual periscope camera system', 'Fast charging', 'Flagship performance'],
        'Apple iPad Pro 13 2024' => ['13-inch Ultra Retina XDR display', 'M4 chip', 'Apple Pencil Pro support', 'Magic Keyboard support'],
        'Samsung Galaxy Tab S9 Ultra' => ['14.6-inch Dynamic AMOLED 2X', 'S Pen included', 'IP68 rating', 'DeX productivity mode'],
        'Huawei MatePad Pro 13.2' => ['13.2-inch flexible OLED display', 'M-Pencil support', 'Keyboard support', 'Slim body'],
        'Dell XPS 13' => ['13-inch InfinityEdge display', 'Intel Core platform', 'Premium aluminum body', 'Compact laptop design'],
        'ASUS Zenbook 14 OLED' => ['14-inch OLED display', 'Thin and light body', 'Long battery life', 'Creator-friendly display'],
        'Sony WH-1000XM5' => ['Noise cancelling headphones', 'Wireless Bluetooth audio', 'Long battery life', 'Soft over-ear comfort'],
        'Logitech MX Master 3S' => ['Quiet clicks', 'MagSpeed scroll wheel', 'Ergonomic shape', 'Multi-device workflow'],
        'Apple iPhone 17 Pro Max' => ['Storage: 256GB / 512GB / 1TB / 2TB', 'A19 Pro chip', '6.9-inch Super Retina XDR display', 'Three 48MP Fusion cameras'],
        'Apple iPhone 17 Pro' => ['Storage: 256GB / 512GB / 1TB', 'A19 Pro chip', '6.3-inch Super Retina XDR display', 'Three 48MP Fusion cameras'],
        'Apple iPhone Air' => ['Storage: 256GB / 512GB / 1TB', 'A19 Pro chip', '6.5-inch OLED display', 'Ultra-thin titanium design'],
        'Apple iPhone 17' => ['Storage: 256GB / 512GB', 'A19 chip', '6.3-inch Super Retina XDR display', '48MP Dual Fusion camera system'],
        'Samsung Galaxy S25 Ultra' => ['Storage: 256GB / 512GB / 1TB with 12GB RAM', '6.9-inch Dynamic AMOLED 2X display', 'Built-in S Pen', '200MP wide camera'],
        'Samsung Galaxy Z Fold7' => ['Storage: 256GB / 512GB / 1TB', '12GB / 16GB RAM options', 'Foldable inner display', '200MP main camera'],
        'Samsung Galaxy Z Flip7' => ['Storage: 256GB / 512GB', '12GB RAM', 'Foldable compact display', 'Galaxy AI features'],
        'OPPO Find X8 Pro' => ['Storage: 16GB + 512GB', 'Dimensity 9400 platform', '5910mAh battery', 'Hasselblad quad camera system'],
        'OPPO Find X8' => ['Storage: 12GB + 256GB / 16GB + 512GB', 'Dimensity 9400 platform', '5630mAh battery', 'Triple 50MP camera system'],
        'vivo X300 Pro' => ['Storage: 12GB + 256GB / 16GB + 512GB / 16GB + 1TB', 'Dimensity 9500 platform', '200MP telephoto camera', '90W wired charging'],
        'Xiaomi 15 Ultra' => ['Storage: 16GB + 512GB / 16GB + 1TB', 'Leica camera system', 'Premium Ultra flagship design', 'Fast wired and wireless charging'],
        'HONOR Magic7 Pro' => ['Storage: 12GB + 512GB', 'Flagship Magic series phone', 'Triple camera system', 'Premium curved display'],
        'Huawei Pura 80 Ultra' => ['Storage: 16GB + 512GB / 16GB + 1TB', 'Pura flagship camera phone', 'Premium XMAGE camera system', 'Fast Huawei SuperCharge support'],
        'Apple AirPods Pro 3' => ['USB-C MagSafe Charging Case', 'Active Noise Cancellation', 'Adaptive Audio and Transparency mode', 'Apple ecosystem pairing'],
        'Apple AirPods 4 with ANC' => ['USB-C Charging Case', 'Active Noise Cancellation', 'Open-ear Apple earbuds', 'Personalized Spatial Audio'],
        'Samsung Galaxy Buds3 Pro' => ['Active Noise Cancellation', 'Hi-Fi wireless audio', 'Galaxy AI features', 'IP-rated daily earbuds'],
        'Sony WF-1000XM5' => ['Industry-leading noise cancelling', 'LDAC high-resolution wireless audio', 'Wireless charging case', 'Compact premium earbuds'],
        'Xiaomi Buds 5 Pro' => ['Active Noise Cancellation', 'Hi-Res wireless audio support', 'Long battery charging case', 'Premium Xiaomi earbuds'],
        'Beats Studio Buds +' => ['True wireless earbuds', 'Active Noise Cancellation', 'Transparency mode', 'Apple and Android pairing support'],
        'Razer BlackWidow V4 Pro 75%' => ['75% wireless gaming keyboard', 'OLED command display', 'Hot-swappable mechanical switches', 'Premium Razer Chroma RGB'],
        'Razer Huntsman V3 Pro TKL' => ['Tenkeyless esports keyboard', 'Analog optical switches', 'Rapid Trigger support', '8K polling performance'],
        'Logitech MX Mechanical' => ['Wireless mechanical keyboard', 'Tactile / linear / clicky switch options', 'Multi-device pairing', 'Backlit low-profile keys'],
        'Keychron Q1 HE' => ['Aluminium custom keyboard', 'Hall effect magnetic switches', 'Wireless and wired modes', 'Adjustable actuation'],
        'ASUS ROG Azoth' => ['75% wireless gaming keyboard', 'OLED status display', 'Hot-swappable switches', 'Tri-mode connectivity'],
        'Apple MacBook Air 13 M4' => ['13-inch Liquid Retina display', 'Apple M4 chip', '16GB / 24GB unified memory options', '256GB / 512GB storage options'],
        'Apple MacBook Air 15 M4' => ['15-inch Liquid Retina display', 'Apple M4 chip', '16GB / 24GB unified memory options', '256GB / 512GB storage options'],
        'Apple MacBook Pro 14 M4' => ['14-inch Liquid Retina XDR display', 'Apple M4 chip', '16GB / 24GB unified memory options', '512GB / 1TB storage options'],
        'Apple MacBook Pro 16 M4 Pro' => ['16-inch Liquid Retina XDR display', 'Apple M4 Pro chip', '24GB / 48GB unified memory options', '512GB / 1TB storage options'],
        'Logitech MX Anywhere 3S' => ['Compact wireless mouse', '8K DPI sensor', 'Quiet clicks', 'Multi-device workflow'],
        'Razer Basilisk V3 Pro' => ['Wireless gaming mouse', 'Focus Pro optical sensor', 'Customizable scroll wheel', 'Chroma RGB lighting'],
        'Razer Viper V3 Pro' => ['Ultra-light esports mouse', 'Wireless gaming performance', 'High polling-rate support', 'Ambidextrous-style shell'],
        'Apple Magic Mouse USB-C' => ['Rechargeable USB-C design', 'Multi-Touch surface', 'macOS gesture support', 'White and black finish options'],
        'Apple Watch Series 11' => ['42mm / 46mm case options', 'GPS and Cellular options', 'Health and fitness tracking', 'Apple ecosystem smartwatch'],
        'Apple Watch SE 3' => ['40mm / 44mm case options', 'GPS and Cellular options', 'Core Apple Watch health features', 'Budget Apple smartwatch'],
        'Apple Watch Ultra 3' => ['49mm titanium case', 'GPS + Cellular', 'Rugged outdoor design', 'Advanced sport and adventure features'],
        'Samsung Galaxy Watch8' => ['40mm / 44mm case options', 'Bluetooth and LTE options', 'Health and sleep tracking', 'Galaxy ecosystem smartwatch'],
        'Samsung Galaxy Watch Ultra' => ['47mm rugged smartwatch', 'LTE model', 'Titanium case styling', 'Outdoor-focused Galaxy Watch'],
        'Xiaomi Watch S4' => ['AMOLED smartwatch display', 'Long battery life', 'Health and workout tracking', 'Interchangeable strap style'],
        'Fitbit Charge 6' => ['Fitness tracker form factor', 'Built-in GPS support', 'Heart rate and sleep tracking', 'Google apps integration'],
        'Apple iPad Air M3' => ['11-inch / 13-inch options', 'Apple M3 chip', '128GB / 256GB / 512GB / 1TB storage options', 'Apple Pencil Pro support'],
        'Apple iPad Pro M4' => ['11-inch / 13-inch Ultra Retina XDR options', 'Apple M4 chip', '256GB / 512GB / 1TB / 2TB storage options', 'Apple Pencil Pro and Magic Keyboard support'],
        'Samsung Galaxy Tab S10 Ultra' => ['Large AMOLED tablet display', '12GB / 16GB memory options', '256GB / 512GB / 1TB storage options', 'S Pen productivity support'],
        'Xiaomi Pad 7 Pro' => ['11.2-inch high refresh display', '8GB / 12GB memory options', '256GB / 512GB storage options', 'Xiaomi HyperOS tablet experience'],
        'Huawei MatePad Pro 13.2' => ['13.2-inch OLED tablet', '12GB memory options', '256GB / 512GB storage options', 'M-Pencil and keyboard support'],
    ];

    if (str_contains($name, 'Case') || str_contains($name, 'Cover')) {
        return ['Official or premium protective case', 'MagSafe / model-specific fit where supported', 'Premium flagship phone compatibility', 'High-end case product'];
    }

    if (str_contains($name, 'Cable') || str_contains($name, 'Adapter') || str_contains($name, 'Charger')) {
        return ['Official or premium charging accessory', 'USB-C compatible', 'Fast charging support depends on device', 'Suitable for current flagship phones'];
    }

    return $specs[$name] ?? ['Premium design', 'Reliable performance', 'Modern connectivity', 'Official market product'];
}
