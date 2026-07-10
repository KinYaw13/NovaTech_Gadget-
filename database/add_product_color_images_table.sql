CREATE TABLE IF NOT EXISTS product_color_images (
  color_id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  color_name VARCHAR(80) NOT NULL,
  color_hex VARCHAR(20) NOT NULL DEFAULT '#d8d8d8',
  image VARCHAR(700) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_product_color_images_product
    FOREIGN KEY (product_id) REFERENCES products(product_id)
    ON DELETE CASCADE
);
