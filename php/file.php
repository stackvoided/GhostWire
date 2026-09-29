<?php
declare(strict_types=1);

define('INIT_CHECK', true);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/security.php';

SecurityConfig::applyHeaders();

$hash = $_GET['name'] ?? '';

if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp|gif|pdf|txt)$/i', $hash)) {
    http_response_code(400);
    exit('Invalid file key');
}

$filePath = DIR_UPLOADS . $hash;

if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    exit('File not found or expired');
}

$mtime = filemtime($filePath);
if ($mtime !== false && (time() - $mtime) > TTL_FILES) {
    @unlink($filePath);
    http_response_code(410);
    exit('Resource expired');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($filePath);

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');

if (strpos($mime, 'image/') !== 0) {
    header('Content-Disposition: attachment; filename="file_' . time() . '.' . pathinfo($hash, PATHINFO_EXTENSION) . '"');
} else {
    header('Content-Disposition: inline');
}

readfile($filePath);
exit;
