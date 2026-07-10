USE novatech_gadgets;

ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS invoice_no VARCHAR(40) NULL AFTER order_id,
  ADD COLUMN IF NOT EXISTS customer_email VARCHAR(160) NULL AFTER shipping_name,
  ADD COLUMN IF NOT EXISTS bank_name VARCHAR(80) NULL AFTER payment_method,
  ADD COLUMN IF NOT EXISTS card_last4 VARCHAR(4) NULL AFTER bank_name,
  ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) NULL AFTER card_last4,
  ADD COLUMN IF NOT EXISTS tax DECIMAL(10,2) NULL AFTER subtotal;

ALTER TABLE orders
  MODIFY order_status ENUM('Pending','Processing','Paid','Completed','Delivered','Cancelled') NOT NULL DEFAULT 'Pending';

ALTER TABLE payments
  ADD COLUMN IF NOT EXISTS bank_name VARCHAR(80) NULL AFTER payment_method,
  ADD COLUMN IF NOT EXISTS card_last4 VARCHAR(4) NULL AFTER bank_name;
