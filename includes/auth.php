<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function require_login(): void
{
    if (!current_user() || current_role() === 'guest') {
        redirect('login.php');
    }
}

function require_customer(): void
{
    require_login();

    if (current_role() !== 'customer') {
        redirect('home.php');
    }
}

function require_admin(): void
{
    if (!current_user()) {
        redirect('../login.php');
    }

    if (!is_admin()) {
        redirect('../login.php');
    }
}

function login_user(array $user): void
{
    $_SESSION['user'] = [
        'user_id' => (int) $user['user_id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
}

function login_guest(): void
{
    $_SESSION['user'] = [
        'user_id' => 0,
        'full_name' => 'Guest',
        'email' => '',
        'role' => 'guest',
    ];
}
