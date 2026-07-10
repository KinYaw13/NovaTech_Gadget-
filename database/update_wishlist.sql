USE novatech_gadgets;

CREATE TABLE IF NOT EXISTS wishlists (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  product_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_customer_product (customer_id, product_id),
  INDEX idx_wishlist_customer (customer_id, created_at),
  INDEX idx_wishlist_product (product_id)
);
