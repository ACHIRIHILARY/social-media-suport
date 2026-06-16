<?php

function sanitizeString($input) {
    if ($input === null) return null;
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function sanitizeEmail($input) {
    if ($input === null) return null;
    $email = filter_var(trim($input), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

function sanitizeInt($input) {
    if ($input === null) return null;
    $int = filter_var(trim($input), FILTER_SANITIZE_NUMBER_INT);
    return filter_var($int, FILTER_VALIDATE_INT) !== false ? (int)$int : null;
}

function getIpAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
