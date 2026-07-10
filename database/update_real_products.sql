USE novatech_gadgets;

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM payments;
DELETE FROM order_items;
DELETE FROM orders;
DELETE FROM cart;
DELETE FROM products;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO products (category_id, product_name, brand, description, price, stock_quantity, image, rating, status) VALUES
(1, 'Apple iPhone 15 Pro Max', 'Apple', 'Premium smartphone with titanium design, A17 Pro chip, USB-C, and advanced camera system.', 1199.00, 18, 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-15-pro-max-1.jpg', 4.8, 'active'),
(1, 'Samsung Galaxy S24 Ultra', 'Samsung', 'Flagship Android smartphone with large AMOLED display, S Pen support, and powerful zoom camera.', 1299.00, 16, 'https://images.samsung.com/co/smartphones/galaxy-s24-ultra/images/galaxy-s24-ultra-highlights-kv.jpg?imbypass=true', 4.9, 'active'),
(1, 'Huawei Pura 70 Ultra', 'Huawei', 'Premium Huawei smartphone with bold camera design and high-end mobile photography features.', 1099.00, 14, 'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-pura70-ultra-1.jpg', 4.7, 'active'),
(1, 'vivo X100 Pro', 'vivo', 'Photography-focused smartphone with ZEISS-style imaging experience and flagship performance.', 899.00, 20, 'https://fdn2.gsmarena.com/vv/pics/vivo/vivo-x100-pro-1.jpg', 4.7, 'active'),
(1, 'OPPO Find X7 Ultra', 'OPPO', 'Premium OPPO flagship phone with elegant design and advanced camera hardware.', 949.00, 15, 'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-find-x7-ultra-1.jpg', 4.6, 'active'),
(3, 'Apple iPad Pro 13 2024', 'Apple', 'Large premium tablet for creativity, study, media, and productivity workflows.', 1299.00, 12, 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/75/IPad_Pro_13-inch_backside.jpg/250px-IPad_Pro_13-inch_backside.jpg', 4.8, 'active'),
(3, 'Samsung Galaxy Tab S9 Ultra', 'Samsung', 'Large-screen Android tablet with AMOLED display and productivity-friendly design.', 1099.00, 11, 'https://images.samsung.com/ps/galaxy-tab-s9/feature/galaxy-tab-s9-ultra-graphite.jpg', 4.7, 'active'),
(3, 'Huawei MatePad Pro 13.2', 'Huawei', 'Slim tablet with a large display, keyboard support, and clean productivity experience.', 899.00, 10, 'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-matepad-pro-13-2-2023-1.jpg', 4.5, 'active'),
(2, 'Dell XPS 13', 'Dell', 'Compact premium laptop with clean aluminum design and strong daily productivity performance.', 1199.00, 9, 'https://upload.wikimedia.org/wikipedia/commons/1/18/Dell_XPS_13_9360.jpg', 4.6, 'active'),
(2, 'ASUS Zenbook 14 OLED', 'ASUS', 'Thin OLED laptop for students and creators who want a bright premium display.', 1099.00, 8, 'https://dlcdnwebimgs.asus.com/gain/1B9B2F88-04EE-4A40-BD1B-4025FD7E7368', 4.5, 'active'),
(5, 'Sony WH-1000XM5', 'Sony', 'Popular wireless noise cancelling headphones with clean sound and premium comfort.', 399.00, 24, 'https://www.sony.com.my/image/5d02da5df552836db894cead8a68f5f4?fmt=png-alpha&wid=720', 4.8, 'active'),
(9, 'Logitech MX Master 3S', 'Logitech', 'Premium productivity mouse with quiet clicks, ergonomic shape, and precise scrolling.', 99.00, 30, 'https://resource.logitech.com/w_800,c_limit,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/mice/mx-master-3s/gallery/mx-master-3s-mouse-top-view-graphite.png', 4.7, 'active');
