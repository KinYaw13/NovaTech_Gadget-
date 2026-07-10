USE novatech_gadgets;

CREATE TABLE IF NOT EXISTS warranty_claims (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  order_id INT NOT NULL,
  order_item_id INT NULL,
  product_name VARCHAR(180) NOT NULL,
  reason VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  image_path VARCHAR(255) NULL,
  status ENUM('Pending','Approved','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  admin_note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customer_claims (customer_id, created_at),
  INDEX idx_order_claims (order_id),
  INDEX idx_claim_status (status)
);
