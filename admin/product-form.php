<?php
$product = $product ?? [];
$currentImage = (string) ($product['image'] ?? '');
$colorRows = $colorRows ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colorRows = [];
    foreach (($_POST['color_name'] ?? []) as $index => $colorName) {
        $colorRows[] = [
            'color_name' => (string) $colorName,
            'color_hex' => (string) ($_POST['color_hex'][$index] ?? '#d8d8d8'),
            'image' => (string) (($_POST['color_image_url'][$index] ?? '') ?: ($_POST['existing_color_image'][$index] ?? '')),
        ];
    }
}

$blankRows = max(4, count($colorRows) + 2);
$adminImageSrc = static function (string $image): string {
    if ($image === '' || preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    return '../' . ltrim($image, '/');
};
?>
<div class="form-grid">
  <div class="field"><label>Product name</label><input name="product_name" required value="<?= e($product['product_name'] ?? '') ?>"></div>
  <div class="field"><label>Brand</label><input name="brand" required value="<?= e($product['brand'] ?? '') ?>"></div>
  <div class="field"><label>Category</label><select name="category_id" required>
    <?php foreach ($categories as $category): ?>
      <option value="<?= (int) $category['category_id'] ?>" <?= (int) ($product['category_id'] ?? 0) === (int) $category['category_id'] ? 'selected' : '' ?>><?= e($category['category_name']) ?></option>
    <?php endforeach; ?>
  </select></div>
  <div class="field"><label>Price</label><input name="price" type="number" step="0.01" min="0" required value="<?= e((string) ($product['price'] ?? '')) ?>"></div>
  <div class="field"><label>Stock quantity</label><input name="stock_quantity" type="number" min="0" required value="<?= e((string) ($product['stock_quantity'] ?? '0')) ?>"></div>
  <div class="field"><label>Rating</label><input name="rating" type="number" step="0.1" min="0" max="5" value="<?= e((string) ($product['rating'] ?? '4.5')) ?>"></div>
  <div class="field"><label>Status</label><select name="status"><option value="active">active</option><option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>inactive</option></select></div>
  <div class="field full"><label>Description</label><textarea name="description"><?= e($product['description'] ?? '') ?></textarea></div>
  <div class="field full">
    <label>Main product image</label>
    <?php if ($currentImage !== ''): ?>
      <div class="upload-preview"><img src="<?= e($adminImageSrc($currentImage)) ?>" alt="Current product image" onerror="this.parentElement.style.display='none';"></div>
    <?php endif; ?>
    <input name="product_image_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
    <input name="image_url" type="text" value="<?= e($currentImage) ?>" placeholder="Or paste an image URL / existing image path">
  </div>
  <div class="field full">
    <label>Color images</label>
    <p class="form-hint">Add one row per color. The product detail image will change to this photo when customers choose that color.</p>
    <div class="color-image-editor">
      <div class="color-image-head"><span>Color</span><span>Swatch</span><span>Photo</span><span>Current</span></div>
      <?php for ($index = 0; $index < $blankRows; $index++): ?>
        <?php
          $row = $colorRows[$index] ?? [];
          $rowName = (string) ($row['color_name'] ?? '');
          $rowHex = (string) ($row['color_hex'] ?? '#d8d8d8');
          $rowImage = (string) ($row['image'] ?? '');
        ?>
        <div class="color-image-row">
          <input name="color_name[]" type="text" value="<?= e($rowName) ?>" placeholder="Black">
          <input name="color_hex[]" type="color" value="<?= e($rowHex ?: '#d8d8d8') ?>">
          <div>
            <input name="color_image_file[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
            <input name="color_image_url[]" type="text" value="<?= e($rowImage) ?>" placeholder="Or paste image URL / existing path">
            <input name="existing_color_image[]" type="hidden" value="<?= e($rowImage) ?>">
          </div>
          <div class="color-image-current">
            <?php if ($rowImage !== ''): ?><img src="<?= e($adminImageSrc($rowImage)) ?>" alt="<?= e($rowName ?: 'Color image') ?>" onerror="this.style.display='none';"><?php endif; ?>
          </div>
        </div>
      <?php endfor; ?>
    </div>
    <button class="btn secondary add-color-row" type="button" data-add-color-row>Add another color</button>
  </div>
</div>
