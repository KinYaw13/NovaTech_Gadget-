USE novatech_gadgets;

UPDATE products SET image='https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-finish-select-202509-6-9inch-cosmicorange?wid=900&hei=900&fmt=png-alpha' WHERE product_name='Apple iPhone 17 Pro Max';
UPDATE products SET image='https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-pro-finish-select-202509-6-3inch-cosmicorange?wid=900&hei=900&fmt=png-alpha' WHERE product_name='Apple iPhone 17 Pro';
UPDATE products SET image='https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-air-finish-select-202509-skyblue?wid=900&hei=900&fmt=png-alpha' WHERE product_name='Apple iPhone Air';
UPDATE products SET image='https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/iphone-17-finish-select-202509-lavender?wid=900&hei=900&fmt=png-alpha' WHERE product_name='Apple iPhone 17';
UPDATE products SET image='https://images.samsung.com/rs/smartphones/galaxy-s25-ultra/buy/02_Gallery/02-1_KV_No-Exclusive-Color/01_Color_Group_KV_image_PC.jpg' WHERE product_name='Samsung Galaxy S25 Ultra';
UPDATE products SET image='https://images.samsung.com/is/image/samsung/assets/cl/smartphones/galaxy-z-fold7/buy/Q7_Global_Color_Group_KV_Jetblack_Blue_Shadow_Silver_Shadow_No-text_PC_1600x864.png' WHERE product_name='Samsung Galaxy Z Fold7';
UPDATE products SET image='https://images.samsung.com/is/image/samsung/assets/cl/smartphones/galaxy-z-flip7/buy/B7_Global_Color_Group_KV_Blue_Shadow_No-text_PC_1600x864.png' WHERE product_name='Samsung Galaxy Z Flip7';
UPDATE products SET image='https://www.oppo.com/content/dam/oppo/common/mkt/v2-2/find-x8-series-en/find-x8-pro/listpage/432-600-white.png' WHERE product_name='OPPO Find X8 Pro';
UPDATE products SET image='https://www.oppo.com/content/dam/oppo/common/mkt/v2-2/find-x8-series-en/find-x8/listpage/436-600-white-v2.png' WHERE product_name='OPPO Find X8';
UPDATE products SET image='https://i02.appmifile.com/492_operatorx_operatorx_opx/02/03/2025/5667c36c15d47b90d0faa7ac23c9f276.png' WHERE product_name='Xiaomi 15 Ultra';
UPDATE products SET image='https://consumer.huawei.com/dam/content/dam/huawei-cbg-site/common/mkt/pdp/phones/pura80-ultra/list-gold.png' WHERE product_name='Huawei Pura 80 Ultra';
UPDATE products SET image='https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MYYV3?wid=1144&hei=1144&fmt=jpeg&qlt=90' WHERE product_name='Apple Silicone Case with MagSafe - iPhone 17 Series';
UPDATE products SET image='https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MA7E4?wid=1144&hei=1144&fmt=jpeg&qlt=90' WHERE product_name='Apple Clear Case with MagSafe - iPhone 17 Series';
UPDATE products SET image='https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MYYX3?wid=1144&hei=1144&fmt=jpeg&qlt=90' WHERE product_name='Apple TechWoven Case with MagSafe - iPhone 17 Pro';
UPDATE products SET image='https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MT0Y3?wid=1144&hei=1144&fmt=jpeg&qlt=90' WHERE product_name='Apple Silicone Case with MagSafe - iPhone 15 Series';
UPDATE products SET image='https://store.storeimages.cdn-apple.com/1/as-images.apple.com/is/HSJT2?wid=890&hei=890&fmt=jpeg&qlt=90' WHERE product_name='Apple 30W USB-C Power Adapter';

DELETE FROM products WHERE product_name IN (
  'CASETiFY Impact Case - iPhone Pro Series',
  'OtterBox Defender Series Case - iPhone Pro Series',
  'Samsung Silicone Case - Galaxy S25 Ultra',
  'Samsung Standing Grip Case - Galaxy S25 Ultra',
  'Samsung Slim S Pen Case - Galaxy Z Fold7',
  'Xiaomi 15 Ultra Photography Kit',
  'Samsung 45W Power Adapter',
  'Samsung USB-C to USB-C Cable 5A',
  'Samsung Galaxy SmartTag2',
  'Samsung Galaxy Buds3 Pro',
  'UGREEN Nexode 100W GaN Charger',
  'Anker 737 Power Bank'
);
