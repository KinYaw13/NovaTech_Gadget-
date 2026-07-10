<?php
declare(strict_types=1);

function normalizeMalaysiaPhone(string $countryCode, string $phone): ?string
{
    $countryCode = trim($countryCode);
    $phone = preg_replace('/[\s-]+/', '', trim($phone));

    if ($phone === '') {
        return '';
    }

    if ($countryCode !== '+60') {
        return null;
    }

    if (!ctype_digit($phone)) {
        return null;
    }

    if (str_starts_with($phone, '0')) {
        $phone = substr($phone, 1);
    }

    $length = strlen($phone);
    if ($length < 9 || $length > 10) {
        return null;
    }

    return '+60' . $phone;
}
