CREATE TABLE IF NOT EXISTS product_requests (
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
);

ALTER TABLE product_requests
  ADD COLUMN IF NOT EXISTS customer_id INT NULL AFTER id,
  ADD COLUMN IF NOT EXISTS requested_by_email VARCHAR(160) NULL AFTER customer_id,
  ADD COLUMN IF NOT EXISTS customer_message TEXT NULL AFTER requested_by_email,
  ADD COLUMN IF NOT EXISTS normalized_query VARCHAR(255) NULL AFTER customer_message,
  ADD COLUMN IF NOT EXISTS brand VARCHAR(100) NULL AFTER product_name,
  ADD COLUMN IF NOT EXISTS category VARCHAR(100) NULL AFTER brand,
  ADD COLUMN IF NOT EXISTS estimated_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER category,
  ADD COLUMN IF NOT EXISTS product_image_url VARCHAR(700) NULL AFTER estimated_price,
  ADD COLUMN IF NOT EXISTS source_url VARCHAR(700) NULL AFTER product_image_url,
  ADD COLUMN IF NOT EXISTS description TEXT NULL AFTER source_url,
  ADD COLUMN IF NOT EXISTS admin_note TEXT NULL AFTER status,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE product_requests
  MODIFY status ENUM('Pending','Approved','Rejected','Added') NOT NULL DEFAULT 'Pending';
