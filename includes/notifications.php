<?php
declare(strict_types=1);

require_once __DIR__ . '/messages.php';

function table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $stmt->execute([$table]);
    return (bool) $stmt->fetchColumn();
}

function safe_count(PDO $pdo, string $sql, array $params = []): int
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $exception) {
        return 0;
    }
}

function customer_notification_counts(PDO $pdo, int $customerId): array
{
    ensure_customer_engagement_schema($pdo);

    return [
        'unread_messages' => safe_count($pdo, "SELECT COUNT(*) FROM messages WHERE customer_id = ? AND receiver_role = 'customer' AND is_read = 0", [$customerId]),
        'active_vouchers' => table_exists($pdo, 'vouchers') ? safe_count($pdo, "SELECT COUNT(*) FROM vouchers WHERE customer_id = ? AND status = 'active' AND expires_at >= NOW()", [$customerId]) : 0,
        'pending_warranty' => table_exists($pdo, 'warranty_claims') ? safe_count($pdo, "SELECT COUNT(*) FROM warranty_claims WHERE customer_id = ? AND status = 'Pending'", [$customerId]) : 0,
    ];
}

function admin_notification_counts(PDO $pdo): array
{
    ensure_customer_engagement_schema($pdo);

    return [
        'unread_messages' => safe_count($pdo, "SELECT COUNT(*) FROM messages WHERE receiver_role = 'admin' AND is_read = 0"),
        'pending_product_requests' => table_exists($pdo, 'product_requests') ? safe_count($pdo, "SELECT COUNT(*) FROM product_requests WHERE status = 'Pending'") : 0,
        'pending_warranty' => table_exists($pdo, 'warranty_claims') ? safe_count($pdo, "SELECT COUNT(*) FROM warranty_claims WHERE status = 'Pending'") : 0,
        'profile_completed' => safe_count($pdo, "SELECT COUNT(*) FROM users WHERE role = 'customer' AND profile_completed = 1 AND profile_completed_notified = 1"),
        'active_discounts' => table_exists($pdo, 'discounts') ? safe_count($pdo, "SELECT COUNT(*) FROM discounts WHERE status = 'active'") : 0,
        'vouchers_sent' => table_exists($pdo, 'vouchers') ? safe_count($pdo, 'SELECT COUNT(*) FROM vouchers') : 0,
    ];
}

function nav_badge(int $count): string
{
    if ($count <= 0) {
        return '';
    }

    return '<span class="nav-badge">' . e((string) $count) . '</span>';
}
