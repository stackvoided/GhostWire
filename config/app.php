<?php
declare(strict_types=1);

if (!defined('INIT_CHECK')) {
    http_response_code(403);
    exit;
}

define('APP_NAME', 'GhostWire');
define('TIMEZONE', 'UTC');
date_default_timezone_set(TIMEZONE);

define('TTL_MESSAGES', 1800);
define('TTL_FILES', 1800);
define('COOKIE_LIFETIME', 2592000);

define('MAX_FILE_SIZE', 8388608);
define('ALLOWED_MIMES', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
    'application/pdf' => 'pdf',
    'text/plain' => 'txt'
]);

define('DIR_ROOT', dirname(__DIR__));
define('DIR_UPLOADS', DIR_ROOT . '/storage_data/');

if (!is_dir(DIR_UPLOADS)) {
    mkdir(DIR_UPLOADS, 0755, true);
}
