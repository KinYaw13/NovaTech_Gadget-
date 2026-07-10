<?php
declare(strict_types=1);

function ensure_product_reviews_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_reviews (
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
    )");
}

function product_review_summary(PDO $pdo, int $productId): array
{
    ensure_product_reviews_schema($pdo);
    $stmt = $pdo->prepare("SELECT COUNT(*) AS review_count, COALESCE(AVG(rating), 0) AS average_rating FROM product_reviews WHERE product_id = ? AND status = 'visible'");
    $stmt->execute([$productId]);
    $summary = $stmt->fetch() ?: ['review_count' => 0, 'average_rating' => 0];

    return [
        'count' => (int) $summary['review_count'],
        'average' => round((float) $summary['average_rating'], 1),
    ];
}

function product_visible_reviews(PDO $pdo, int $productId): array
{
    ensure_product_reviews_schema($pdo);
    $stmt = $pdo->prepare("
        SELECT pr.*, u.full_name
        FROM product_reviews pr
        JOIN users u ON u.user_id = pr.customer_id
        WHERE pr.product_id = ? AND pr.status = 'visible'
        ORDER BY pr.created_at DESC
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function reviewable_order_items(PDO $pdo, int $customerId, int $productId): array
{
    ensure_product_reviews_schema($pdo);
    $stmt = $pdo->prepare("
        SELECT oi.order_item_id, oi.order_id, oi.product_name, oi.selected_options, o.created_at
        FROM order_items oi
        JOIN orders o ON o.order_id = oi.order_id
        LEFT JOIN product_reviews pr ON pr.order_item_id = oi.order_item_id
        WHERE o.user_id = ?
          AND oi.product_id = ?
          AND o.order_status <> 'Cancelled'
          AND pr.id IS NULL
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$customerId, $productId]);
    return $stmt->fetchAll();
}

function customer_can_review_product(PDO $pdo, int $customerId, int $productId): bool
{
    return count(reviewable_order_items($pdo, $customerId, $productId)) > 0;
}
