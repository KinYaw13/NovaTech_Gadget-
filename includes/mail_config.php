<?php
declare(strict_types=1);

/*
 * NovaTech Gadgets Gmail SMTP configuration.
 *
 * Replace this Gmail address with your own Gmail.
 * Replace this password with your Gmail App Password, not your normal Gmail password.
 * To use Gmail SMTP, enable 2-Step Verification and create an App Password in Google Account.
 *
 * This single configuration is used by:
 * - checkout payment OTP email
 * - forgot password OTP email
 * - invoice email
 * - voucher / customer notification email where available
 *
 * Gmail SMTP settings:
 * Host: smtp.gmail.com
 * Port: 587
 * Encryption: TLS
 * SMTPAuth: true
 */
return [
    'host' => 'smtp.gmail.com',
    'username' => 'novatech2408@gmail.com',
    'password' => 'ogxk rsxv kshp bane',
    'port' => 587,
    'encryption' => 'tls',
    'from_email' => 'novatech2408@gmail.com',
    'from_name' => 'NovaTech Gadgets',
];
