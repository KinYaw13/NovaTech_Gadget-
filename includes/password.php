<?php
declare(strict_types=1);

function validatePasswordStrength(string $password): bool
{
    if (strlen($password) < 8) {
        return false;
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }

    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }

    if (preg_match('/\s/', $password)) {
        return false;
    }

    return preg_match_all('/\d/', $password) >= 6;
}

function passwordStrengthMessage(): string
{
    return 'Password must be at least 8 characters, include uppercase and lowercase letters, include at least 6 numbers, and contain no spaces.';
}
