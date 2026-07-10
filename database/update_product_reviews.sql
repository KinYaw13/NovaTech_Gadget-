USE novatech_gadgets;

CREATE TABLE IF NOT EXISTS product_reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  customer_id INT NOT NULL,
  order_id INT NOT NULL,
  order_item_id INT NOT NULL,
  rating TINYINT NOT NULL,
  comment TEXT NOT NULL,
  status ENUM('visible','hidden') NOT NULL DEFAULT 'visible',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_review_order_item (order_item_id),
  INDEX idx_product_reviews (product_id, status, created_at),
  INDEX idx_customer_reviews (customer_id, created_at),
  CONSTRAINT chk_product_review_rating CHECK (rating BETWEEN 1 AND 5)
);
