<?php
declare(strict_types=1);

if (!defined('INIT_CHECK')) {
    http_response_code(403);
    exit;
}

function sanitize_str(string $input, int $maxLen = 256): string
{
    $clean = trim($input);
    $clean = strip_tags($clean);
    $clean = htmlspecialchars($clean, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return mb_substr($clean, 0, $maxLen, 'UTF-8');
}

function validate_key(string $key): bool
{
    $len = mb_strlen($key, 'UTF-8');
    if ($len < 10 || $len > 30) {
        return false;
    }
    return (bool)preg_match('/^[a-zA-Z0-9\s\-_!@#$%^&*()]+$/u', $key);
}

function derive_room_id(string $key): string
{
    return hash_hmac('sha256', trim($key), 'GW_SALT_v2_2026');
}

function respond_json(array $payload, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
