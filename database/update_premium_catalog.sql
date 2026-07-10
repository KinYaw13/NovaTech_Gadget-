USE novatech_gadgets;

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM payments;
DELETE FROM order_items;
DELETE FROM orders;
DELETE FROM cart;
DELETE FROM products;
DELETE FROM categories;
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE products MODIFY image VARCHAR(700);
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE products AUTO_INCREMENT = 1;

INSERT INTO categories (category_name, description) VALUES
('Smartphones', 'Real flagship phones sold through official and mall sellers'),
('Laptops', 'Portable computers for work and study'),
('Tablets', 'Touch devices for notes and entertainment'),
('Smartwatches', 'Wearable health and notification devices'),
('Earbuds', 'Wireless personal audio devices'),
('Keyboards', 'Productivity keyboards and desk tools'),
('Mice', 'Precision mice for productivity'),
('Chargers', 'Official and premium charging accessories'),
('Phone Cases', 'Official and premium cases for iPhone and flagship phones'),
('Accessories', 'Official and premium add-ons for a complete setup');

INSERT INTO products (category_id, product_name, brand, description, price, stock_quantity, image, rating, status) VALUES
(1, 'Apple iPhone 17 Pro Max', 'Apple', 'Config: 256GB / 512GB / 1TB / 2TB. Colors: Cosmic Orange, Deep Blue, Silver.', 1599.00, 10, 'https://www.apple.com/newsroom/images/2025/09/apple-unveils-iphone-17-pro-and-iphone-17-pro-max-the-most-powerful-and-advanced-pro-models-ever/article/Apple-iPhone-17-Pro-hero_big.jpg.large.jpg', 4.9, 'active'),
(1, 'Apple iPhone 17 Pro', 'Apple', 'Config: 256GB / 512GB / 1TB. Colors: Cosmic Orange, Deep Blue, Silver.', 1399.00, 12, 'https://www.apple.com/newsroom/images/2025/09/apple-unveils-iphone-17-pro-and-iphone-17-pro-max-the-most-powerful-and-advanced-pro-models-ever/article/Apple-iPhone-17-Pro-hero_big.jpg.large.jpg', 4.9, 'active'),
(1, 'Apple iPhone Air', 'Apple', 'Config: 256GB / 512GB / 1TB. Colors: Sky Blue, Light Gold, Cloud White, Space Black.', 1299.00, 12, 'https://www.apple.com/newsroom/images/2025/09/introducing-iphone-air-a-powerful-new-iphone-with-a-breakthrough-design/article/Apple-iPhone-Air-hero_big.jpg.large.jpg', 4.8, 'active'),
(1, 'Apple iPhone 17', 'Apple', 'Config: 256GB / 512GB. Colors: Lavender, Sage, Mist Blue, White, Black.', 999.00, 18, 'https://www.apple.com/newsroom/images/2025/09/apple-debuts-iphone-17/article/Apple-iPhone-17-hero_big.jpg.large.jpg', 4.8, 'active'),
(1, 'Apple iPhone 16', 'Apple', 'Config: 128GB / 256GB / 512GB. Colors: Ultramarine, Teal, Pink, White, Black.', 899.00, 20, 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-16-1.jpg', 4.7, 'active'),
(1, 'Apple iPhone 15 Pro Max', 'Apple', 'Config: 256GB / 512GB / 1TB. Colors: Natural Titanium, Blue Titanium, White Titanium, Black Titanium.', 1199.00, 18, 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-15-pro-max-1.jpg', 4.8, 'active'),
(1, 'Samsung Galaxy S25 Ultra', 'Samsung', 'Config: 12GB+256GB / 12GB+512GB / 12GB+1TB. Colors: Titanium Silverblue, Titanium Black, Titanium Gray, Titanium Whitesilver, Titanium Jadegreen, Titanium Pinkgold.', 1299.00, 16, 'https://images.samsung.com/levant/smartphones/galaxy-s25-ultra/buy/kv_group_PC_v2.jpg?imbypass=true', 4.9, 'active'),
(1, 'Samsung Galaxy S24 Ultra', 'Samsung', 'Config: 12GB+256GB / 12GB+512GB / 12GB+1TB. Colors: Titanium Black, Titanium Gray, Titanium Violet, Titanium Yellow.', 1099.00, 16, 'https://images.samsung.com/co/smartphones/galaxy-s24-ultra/images/galaxy-s24-ultra-highlights-kv.jpg?imbypass=true', 4.8, 'active'),
(1, 'Samsung Galaxy Z Fold7', 'Samsung', 'Config: 12GB+256GB / 12GB+512GB / 16GB+1TB. Colors: Blue Shadow, Jetblack, Silver Shadow, Mint.', 1899.00, 8, 'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-z-fold7-1.jpg', 4.8, 'active'),
(1, 'Samsung Galaxy Z Flip7', 'Samsung', 'Config: 12GB+256GB / 12GB+512GB. Colors: Blue Shadow, Jetblack, Coralred, Mint.', 1199.00, 10, 'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-z-flip7-1.jpg', 4.7, 'active'),
(1, 'OPPO Find X8 Pro', 'OPPO', 'Config: 16GB+512GB. Colors: Pearl White, Space Black.', 999.00, 12, 'https://image.oppo.com/content/dam/oppo/common/mkt/v2-2/find-x8-pro-en/images-color-konka-l-0.jpg', 4.7, 'active'),
(1, 'OPPO Find X8', 'OPPO', 'Config: 12GB+256GB / 16GB+512GB. Colors: Star Grey, Space Black, Shell Pink.', 799.00, 14, 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/ea/Oppo_Find_X8.jpg/250px-Oppo_Find_X8.jpg', 4.6, 'active'),
(1, 'vivo X300 Pro', 'vivo', 'Config: 12GB+256GB / 16GB+512GB / 16GB+1TB. Colors: Phantom Black, Mist Blue, Dune Brown, Cloud White.', 1099.00, 10, 'https://fdn2.gsmarena.com/vv/pics/vivo/vivo-x300-pro-1.jpg', 4.7, 'active'),
(1, 'vivo X200 Pro', 'vivo', 'Config: 16GB+512GB. Colors: Titanium Grey, Carbon Black, Blue.', 899.00, 13, 'https://fdn2.gsmarena.com/vv/pics/vivo/vivo-x200-pro-1.jpg', 4.6, 'active'),
(1, 'Xiaomi 15 Ultra', 'Xiaomi', 'Config: 16GB+512GB / 16GB+1TB. Colors: Black, White, Silver Chrome.', 1199.00, 9, 'https://fdn2.gsmarena.com/vv/pics/xiaomi/xiaomi-15-ultra-1.jpg', 4.8, 'active'),
(1, 'HONOR Magic7 Pro', 'HONOR', 'Config: 12GB+512GB. Colors: Lunar Shadow Grey, Breeze Blue, Black.', 949.00, 11, 'https://fdn2.gsmarena.com/vv/pics/honor/honor-magic7-pro-1.jpg', 4.6, 'active'),
(1, 'Huawei Pura 80 Ultra', 'Huawei', 'Config: 16GB+512GB / 16GB+1TB. Colors: Gold, Black, White.', 1299.00, 8, 'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-pura-80-ultra-1.jpg', 4.7, 'active'),

(9, 'Apple Silicone Case with MagSafe - iPhone 17 Series', 'Apple', 'Fits: iPhone 17 Pro Max, iPhone 17 Pro, iPhone 17, iPhone Air, iPhone 17e. Colors: Black, Orange, Blue, Green, Pink.', 59.00, 35, 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-silicone-case-orange?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.7, 'active'),
(9, 'Apple Clear Case with MagSafe - iPhone 17 Series', 'Apple', 'Fits: iPhone 17 Pro Max, iPhone 17 Pro, iPhone 17, iPhone Air. Color: Clear.', 59.00, 32, 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-clear-case?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.6, 'active'),
(9, 'Apple TechWoven Case with MagSafe - iPhone 17 Pro', 'Apple', 'Fits: iPhone 17 Pro Max and iPhone 17 Pro. Colors: Orange, Blue, Black, Green.', 79.00, 24, 'https://www.apple.com/newsroom/images/2025/09/apple-unveils-iphone-17-pro-and-iphone-17-pro-max-the-most-powerful-and-advanced-pro-models-ever/article/Apple-iPhone-17-Pro-accessories_big.jpg.large.jpg', 4.7, 'active'),
(9, 'Apple Silicone Case with MagSafe - iPhone 16 Series', 'Apple', 'Fits: iPhone 16 Pro Max, iPhone 16 Pro, iPhone 16 Plus, iPhone 16. Colors: Black, Denim, Lake Green, Fuchsia.', 49.00, 28, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MYYV3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.6, 'active'),
(9, 'Apple Clear Case with MagSafe - iPhone 16 Series', 'Apple', 'Fits: iPhone 16 Pro Max, iPhone 16 Pro, iPhone 16 Plus, iPhone 16. Color: Clear.', 49.00, 28, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MA7E4?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.6, 'active'),
(9, 'Apple Silicone Case with MagSafe - iPhone 15 Series', 'Apple', 'Fits: iPhone 15 Pro Max, iPhone 15 Pro, iPhone 15 Plus, iPhone 15. Colors: Black, Blue, Pink, Green.', 49.00, 26, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MT113?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.5, 'active'),
(9, 'CASETiFY Impact Case - iPhone Pro Series', 'CASETiFY', 'Fits: iPhone 17 / 16 / 15 Pro series. Premium high-price case with printed design options.', 89.00, 18, 'https://cdn.shopify.com/s/files/1/0094/6326/7379/files/casetify-impact-case.jpg', 4.5, 'active'),
(9, 'OtterBox Defender Series Case - iPhone Pro Series', 'OtterBox', 'Fits: iPhone 17 / 16 / 15 Pro series. Heavy-duty premium protection case.', 79.00, 18, 'https://images.ctfassets.net/9n7a4r8lzj7a/7lMIUs7USoYfXTU0D3tDpd/7f4d218be04d4355c5a2a0dded8bd78c/otterbox-defender-series.jpg', 4.5, 'active'),
(9, 'Samsung Silicone Case - Galaxy S25 Ultra', 'Samsung', 'Fits: Galaxy S25 Ultra flagship only. Colors: Black, Blue, White.', 49.00, 22, 'https://images.samsung.com/eg/smartphones/galaxy-s25-ultra/accessories/images/galaxy-s25-ultra-accessories-silicone-case.jpg?imbypass=true', 4.6, 'active'),
(9, 'Samsung Standing Grip Case - Galaxy S25 Ultra', 'Samsung', 'Fits: Galaxy S25 Ultra flagship only. Colors: Black, White, Blue.', 69.00, 18, 'https://images.samsung.com/eg/smartphones/galaxy-s25-ultra/accessories/images/galaxy-s25-ultra-accessories-standing-grip-case.jpg?imbypass=true', 4.6, 'active'),
(9, 'Samsung Slim S Pen Case - Galaxy Z Fold7', 'Samsung', 'Fits: Galaxy Z Fold7 flagship foldable only. Colors: Black, Blue Shadow.', 99.00, 14, 'https://images.samsung.com/cl/smartphones/galaxy-z-fold7/buy/acc_slim_s_pen_case_PC.jpg?imbypass=true', 4.6, 'active'),
(9, 'Xiaomi 15 Ultra Photography Kit', 'Xiaomi', 'Fits: Xiaomi 15 Ultra flagship only. Premium camera grip and protective accessory kit.', 199.00, 12, 'https://i02.appmifile.com/mi-com-product/fly-birds/xiaomi-15-ultra/pc/photography-kit.png', 4.7, 'active'),

(8, 'Apple 20W USB-C Power Adapter', 'Apple', 'Official Apple USB-C charger for iPhone and iPad. Color: White.', 19.00, 50, 'https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/MWVV3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.7, 'active'),
(8, 'Apple 30W USB-C Power Adapter', 'Apple', 'Official Apple USB-C charger for iPhone, iPad, and light MacBook Air charging. Color: White.', 39.00, 35, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MW2G3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.7, 'active'),
(8, 'Apple 240W USB-C Charge Cable 2m', 'Apple', 'Official Apple USB-C charge cable for iPhone, iPad, and MacBook. Color: White.', 29.00, 45, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MU2G3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.7, 'active'),
(8, 'Apple MagSafe Charger', 'Apple', 'Official magnetic wireless charger for MagSafe iPhone models. Color: White.', 39.00, 40, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MHXH3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.8, 'active'),
(10, 'Apple AirPods Pro 2 USB-C', 'Apple', 'Official Apple premium earbuds with active noise cancellation and USB-C charging case.', 249.00, 28, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MTJV3?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.8, 'active'),
(10, 'Apple AirTag 4 Pack', 'Apple', 'Official Apple tracker pack for keys, bags, and daily items.', 99.00, 30, 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MX542?wid=1144&hei=1144&fmt=jpeg&qlt=90', 4.7, 'active'),
(8, 'Samsung 45W Power Adapter', 'Samsung', 'Official Samsung fast charger for Galaxy S Ultra, tablets, and USB-C devices. Color: Black.', 49.00, 35, 'https://images.samsung.com/is/image/samsung/p6pim/my/ep-t4511xbegww/gallery/my-45w-power-adapter-ep-t4511xbegww-535841966?$650_519_PNG$', 4.7, 'active'),
(8, 'Samsung USB-C to USB-C Cable 5A', 'Samsung', 'Official Samsung cable for fast charging supported Galaxy devices. Color: Black.', 19.00, 45, 'https://images.samsung.com/is/image/samsung/p6pim/my/ep-dx510jbegww/gallery/my-usb-c-to-usb-c-cable-5a-ep-dx510jbegww-535604074?$650_519_PNG$', 4.6, 'active'),
(10, 'Samsung Galaxy SmartTag2', 'Samsung', 'Official Samsung tracker accessory for keys, bags, and everyday items.', 29.00, 38, 'https://images.samsung.com/is/image/samsung/p6pim/my/ei-t5600bwegww/gallery/my-galaxy-smarttag2-ei-t5600bwegww-537117803?$650_519_PNG$', 4.6, 'active'),
(10, 'Samsung Galaxy Buds3 Pro', 'Samsung', 'Official premium Samsung earbuds with active noise cancellation.', 249.00, 24, 'https://images.samsung.com/is/image/samsung/p6pim/my/sm-r630nzaaxme/gallery/my-galaxy-buds3-pro-r630-sm-r630nzaaxme-542251056?$650_519_PNG$', 4.7, 'active'),
(8, 'UGREEN Nexode 100W GaN Charger', 'UGREEN', 'Premium multi-port GaN charger for phones, tablets, and laptops. Color: Grey.', 79.00, 30, 'https://static.ugreen.com/ugreen/products/100w-gan-fast-charger.jpg', 4.6, 'active'),
(8, 'Anker 737 Power Bank', 'Anker', 'Premium high-capacity power bank for phones, tablets, and laptops. Color: Black.', 129.00, 18, 'https://d2211byn0pk9fi.cloudfront.net/spree/products/150016/product/A1289H11_TD01_V1.jpg', 4.7, 'active');
