USE novatech_gadgets;

ALTER TABLE products
  ADD COLUMN IF NOT EXISTS warranty_period VARCHAR(80) NOT NULL DEFAULT '1 Year',
  ADD COLUMN IF NOT EXISTS warranty_type VARCHAR(120) NOT NULL DEFAULT 'Manufacturer Warranty',
  ADD COLUMN IF NOT EXISTS warranty_description TEXT NULL;

UPDATE products
SET warranty_period = COALESCE(NULLIF(warranty_period, ''), '1 Year'),
    warranty_type = COALESCE(NULLIF(warranty_type, ''), 'Manufacturer Warranty'),
    warranty_description = COALESCE(NULLIF(warranty_description, ''), 'Covers manufacturer defects under normal use. Physical damage, liquid damage, misuse and unauthorized repair are not covered.');

UPDATE products
SET warranty_period = '2 Years',
    warranty_type = 'Manufacturer Warranty',
    warranty_description = 'Covers manufacturer defects under normal use for laptops and tablets. Battery wear, accidental damage, liquid damage and unauthorized repair are not covered.'
WHERE product_name LIKE '%MacBook%'
   OR product_name LIKE '%XPS%'
   OR product_name LIKE '%Zenbook%'
   OR product_name LIKE '%iPad%'
   OR product_name LIKE '%Tab%'
   OR product_name LIKE '%MatePad%'
   OR product_name LIKE '%Pad%';

ALTER TABLE order_items
  ADD COLUMN IF NOT EXISTS product_category VARCHAR(100) NULL AFTER product_id,
  ADD COLUMN IF NOT EXISTS product_name VARCHAR(180) NULL AFTER product_category,
  ADD COLUMN IF NOT EXISTS warranty_period VARCHAR(80) NULL AFTER selected_options,
  ADD COLUMN IF NOT EXISTS warranty_type VARCHAR(120) NULL AFTER warranty_period,
  ADD COLUMN IF NOT EXISTS warranty_description TEXT NULL AFTER warranty_type;
