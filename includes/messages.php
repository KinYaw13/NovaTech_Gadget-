<?php
declare(strict_types=1);

function ensure_customer_engagement_schema(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }

    $queries = [
        "CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            conversation_id VARCHAR(80) NOT NULL,
            sender_id INT NULL,
            sender_role VARCHAR(20) NOT NULL,
            receiver_id INT NULL,
            receiver_role VARCHAR(20) NOT NULL,
            customer_id INT NOT NULL,
            admin_id INT NULL,
            subject VARCHAR(160) NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_customer_messages (customer_id, created_at),
            INDEX idx_conversation (conversation_id)
        )",
        "CREATE TABLE IF NOT EXISTS vouchers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(40) NOT NULL UNIQUE,
            customer_id INT NOT NULL,
            voucher_type VARCHAR(80) NOT NULL,
            discount_type VARCHAR(20) NOT NULL DEFAULT 'fixed',
            discount_value DECIMAL(10,2) NOT NULL DEFAULT 15.00,
            min_order_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_by_admin_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            INDEX idx_customer_vouchers (customer_id, status)
        )",
        "CREATE TABLE IF NOT EXISTS discounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            discount_name VARCHAR(140) NOT NULL,
            discount_type VARCHAR(20) NOT NULL,
            discount_value DECIMAL(10,2) NOT NULL,
            applies_to VARCHAR(20) NOT NULL,
            category VARCHAR(100) NULL,
            product_id INT NULL,
            start_date DATETIME NULL,
            end_date DATETIME NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_discount_status (status, applies_to)
        )",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_completed TINYINT(1) NOT NULL DEFAULT 0",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_completed_notified TINYINT(1) NOT NULL DEFAULT 0",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS voucher_code VARCHAR(40) NULL",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00",
    ];

    foreach ($queries as $query) {
        try {
            $pdo->exec($query);
        } catch (Throwable $exception) {
            // Manual SQL migration is also provided for older MariaDB versions.
        }
    }

    $done = true;
}

function customer_conversation_id(int $customerId): string
{
    return 'customer_' . $customerId . '_admin';
}

function admin_user_id(PDO $pdo): ?int
{
    $stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'admin' ORDER BY user_id ASC LIMIT 1");
    $id = $stmt->fetchColumn();
    return $id ? (int) $id : null;
}

function create_message(PDO $pdo, int $customerId, ?int $senderId, string $senderRole, ?int $receiverId, string $receiverRole, string $subject, string $message, ?int $adminId = null): void
{
    ensure_customer_engagement_schema($pdo);
    $stmt = $pdo->prepare('INSERT INTO messages (conversation_id, sender_id, sender_role, receiver_id, receiver_role, customer_id, admin_id, subject, message, is_read) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)');
    $stmt->execute([
        customer_conversation_id($customerId),
        $senderId,
        $senderRole,
        $receiverId,
        $receiverRole,
        $customerId,
        $adminId,
        $subject,
        $message,
    ]);
}

function send_welcome_profile_message(PDO $pdo, int $customerId, string $customerName): void
{
    $adminId = admin_user_id($pdo);
    create_message(
        $pdo,
        $customerId,
        $adminId,
        'system',
        $customerId,
        'customer',
        'Complete Your NovaTech Profile',
        'Welcome to NovaTech Gadgets! Please complete your profile information, including your phone number and address, so we can process your orders faster and provide better support.',
        $adminId
    );
}

function is_profile_complete(array $profile): bool
{
    return trim((string) ($profile['full_name'] ?? '')) !== ''
        && trim((string) ($profile['email'] ?? '')) !== ''
        && trim((string) ($profile['phone'] ?? '')) !== ''
        && trim((string) ($profile['address'] ?? '')) !== '';
}

function update_profile_completion(PDO $pdo, int $customerId): void
{
    ensure_customer_engagement_schema($pdo);
    $stmt = $pdo->prepare("SELECT user_id, full_name, email, phone, address, profile_completed, profile_completed_notified FROM users WHERE user_id = ? AND role = 'customer'");
    $stmt->execute([$customerId]);
    $profile = $stmt->fetch();
    if (!$profile) {
        return;
    }

    $complete = is_profile_complete($profile);
    if ($complete) {
        $pdo->prepare('UPDATE users SET profile_completed = 1 WHERE user_id = ?')->execute([$customerId]);
        if ((int) ($profile['profile_completed_notified'] ?? 0) === 0) {
            $adminId = admin_user_id($pdo);
            create_message(
                $pdo,
                $customerId,
                $customerId,
                'system',
                $adminId,
                'admin',
                'Customer Profile Completed',
                'Customer ' . $profile['full_name'] . ' has completed their profile information.',
                $adminId
            );
            $pdo->prepare('UPDATE users SET profile_completed_notified = 1 WHERE user_id = ?')->execute([$customerId]);
        }
    } else {
        $pdo->prepare('UPDATE users SET profile_completed = 0 WHERE user_id = ?')->execute([$customerId]);
    }
}
