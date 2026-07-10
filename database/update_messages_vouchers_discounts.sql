USE novatech_gadgets;

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  conversation_id VARCHAR(80) NOT NULL,
  sender_id INT NULL,
  sender_role VARCHAR(20) NOT NULL,
  receiver_id INT NULL,
  receiver_role VARCHAR(20) NOT NULL,
  customer_id INT NOT NULL,
  admin_id INT NULL,
  subject VARCHAR(160) NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_customer_messages (customer_id, created_at),
  INDEX idx_conversation (conversation_id)
);

CREATE TABLE IF NOT EXISTS vouchers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  customer_id INT NOT NULL,
  voucher_type VARCHAR(80) NOT NULL,
  discount_type VARCHAR(20) NOT NULL DEFAULT 'fixed',
  discount_value DECIMAL(10,2) NOT NULL DEFAULT 15.00,
  min_order_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_by_admin_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  INDEX idx_customer_vouchers (customer_id, status)
);

CREATE TABLE IF NOT EXISTS discounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  discount_name VARCHAR(140) NOT NULL,
  discount_type VARCHAR(20) NOT NULL,
  discount_value DECIMAL(10,2) NOT NULL,
  applies_to VARCHAR(20) NOT NULL,
  category VARCHAR(100) NULL,
  product_id INT NULL,
  start_date DATETIME NULL,
  end_date DATETIME NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_discount_status (status, applies_to)
);

ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_completed TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_completed_notified TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS voucher_code VARCHAR(40) NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00;
