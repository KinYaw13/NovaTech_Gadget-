<?php

function upload_product_image(array $file, string $prefix = 'product'): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please choose a valid image file.');
    }

    if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Image must be 5MB or smaller.');
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $info = @getimagesize($tmpPath);
    if ($info === false) {
        throw new RuntimeException('Please upload a real image file.');
    }

    $extensions = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
        IMAGETYPE_GIF => 'gif',
    ];
    $type = (int) ($info[2] ?? 0);
    if (!isset($extensions[$type])) {
        throw new RuntimeException('Image must be JPG, PNG, WEBP, or GIF.');
    }

    $uploadDir = dirname(__DIR__) . '/images/uploads/products';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Could not create product upload folder.');
    }

    $safePrefix = preg_replace('/[^a-z0-9]+/i', '-', strtolower($prefix)) ?: 'product';
    $filename = $safePrefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extensions[$type];
    $target = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpPath, $target)) {
        throw new RuntimeException('Could not save uploaded image.');
    }

    return 'images/uploads/products/' . $filename;
}

function uploaded_file_from_group(array $files, int $index): array
{
    return [
        'name' => $files['name'][$index] ?? '',
        'type' => $files['type'][$index] ?? '',
        'tmp_name' => $files['tmp_name'][$index] ?? '',
        'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
        'size' => $files['size'][$index] ?? 0,
    ];
}

function save_product_color_images(PDO $pdo, int $productId, array $post, array $files): void
{
    $names = $post['color_name'] ?? [];
    $hexes = $post['color_hex'] ?? [];
    $urls = $post['color_image_url'] ?? [];
    $existingImages = $post['existing_color_image'] ?? [];
    $imageFiles = $files['color_image_file'] ?? [];

    $pdo->prepare('DELETE FROM product_color_images WHERE product_id = ?')->execute([$productId]);
    $insert = $pdo->prepare('INSERT INTO product_color_images (product_id, color_name, color_hex, image) VALUES (?, ?, ?, ?)');

    foreach ($names as $index => $rawName) {
        $colorName = trim((string) $rawName);
        $colorHex = trim((string) ($hexes[$index] ?? ''));
        $imageUrl = trim((string) ($urls[$index] ?? ''));
        $existingImage = trim((string) ($existingImages[$index] ?? ''));

        if (isset($imageFiles['name'][$index])) {
            $uploaded = upload_product_image(uploaded_file_from_group($imageFiles, (int) $index), 'color-' . $productId . '-' . $colorName);
            if ($uploaded !== '') {
                $imageUrl = $uploaded;
            }
        }

        if ($imageUrl === '' && $existingImage !== '') {
            $imageUrl = $existingImage;
        }

        if ($colorName === '' && $imageUrl === '') {
            continue;
        }

        if ($colorName === '') {
            throw new RuntimeException('Every color image row needs a color name.');
        }

        if ($colorHex === '') {
            $colorHex = '#d8d8d8';
        }

        $insert->execute([$productId, $colorName, $colorHex, $imageUrl]);
    }
}

function admin_product_color_rows(PDO $pdo, int $productId): array
{
    if ($productId <= 0) {
        return [];
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM product_color_images WHERE product_id = ? ORDER BY color_id');
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    } catch (PDOException) {
        return [];
    }
}
