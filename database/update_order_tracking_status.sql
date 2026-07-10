USE novatech_gadgets;

ALTER TABLE orders
  MODIFY order_status ENUM('Paid','Processing','Packed','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Paid';
