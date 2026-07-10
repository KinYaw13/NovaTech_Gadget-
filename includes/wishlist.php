<?php
declare(strict_types=1);

function ensure_wishlist_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_customer_product (customer_id, product_id),
        INDEX idx_wishlist_customer (customer_id, created_at),
        INDEX idx_wishlist_product (product_id)
    )");
}

function wishlist_count(PDO $pdo, int $customerId): int
{
    ensure_wishlist_schema($pdo);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM wishlists WHERE customer_id = ?');
    $stmt->execute([$customerId]);
    return (int) $stmt->fetchColumn();
}

function is_in_wishlist(PDO $pdo, int $customerId, int $productId): bool
{
    ensure_wishlist_schema($pdo);
    $stmt = $pdo->prepare('SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ? LIMIT 1');
    $stmt->execute([$customerId, $productId]);
    return (bool) $stmt->fetch();
}

function add_to_wishlist(PDO $pdo, int $customerId, int $productId): void
{
    ensure_wishlist_schema($pdo);
    $stmt = $pdo->prepare('INSERT IGNORE INTO wishlists (customer_id, product_id) VALUES (?, ?)');
    $stmt->execute([$customerId, $productId]);
}

function remove_from_wishlist(PDO $pdo, int $customerId, int $productId): void
{
    ensure_wishlist_schema($pdo);
    $stmt = $pdo->prepare('DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?');
    $stmt->execute([$customerId, $productId]);
}

function wishlist_items(PDO $pdo, int $customerId): array
{
    ensure_wishlist_schema($pdo);
    $stmt = $pdo->prepare("
        SELECT w.id AS wishlist_id, w.created_at AS saved_at, p.*, c.category_name
        FROM wishlists w
        JOIN products p ON p.product_id = w.product_id
        LEFT JOIN categories c ON c.category_id = p.category_id
        WHERE w.customer_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$customerId]);
    return $stmt->fetchAll();
}
