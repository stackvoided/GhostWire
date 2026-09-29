<?php
declare(strict_types=1);

define('INIT_CHECK', true);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/includes/storage.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

SecurityConfig::applyHeaders();

StorageGarbageCollector::purgeExpired();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    $room   = $_GET['room'] ?? '';

    if ($action === 'fetch' && !empty($room)) {
        $messages = class_exists('StorageRepository') && method_exists('StorageRepository', 'get')
            ? StorageRepository::get($room)
            : (class_exists('Storage') && method_exists('Storage', 'get') ? Storage::get($room) : []);

        respond_json(['status' => 'ok', 'data' => $messages]);
    }

    respond_json(['error' => 'Invalid GET parameters'], 400);
}

if ($method !== 'POST') {
    respond_json(['error' => 'Method Not Allowed'], 405);
}

$csrf = $_POST['csrf_token'] ?? '';
if (!SecurityConfig::verifyCsrfToken($csrf)) {
    respond_json(['error' => 'Invalid transaction token'], 403);
}

$action = $_POST['action'] ?? '';

if ($action === 'dispatch') {
    $alias  = sanitize_str($_POST['alias'] ?? '', 32);
    $key    = $_POST['key'] ?? '';
    $text   = sanitize_str($_POST['payload'] ?? '', 2048);
    $avatar = sanitize_str($_POST['avatar_ref'] ?? '', 255);

    if (empty($alias) || !validate_key($key)) {
        respond_json(['error' => 'Validation error'], 422);
    }

    $fileData = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $fileData = UploadHandler::process($_FILES['attachment']);
        } catch (Exception $e) {
            respond_json(['error' => $e->getMessage()], 400);
        }
    }

    if (empty($text) && $fileData === null) {
        respond_json(['error' => 'Empty payload'], 400);
    }

    $roomId = derive_room_id($key);

    $out = [
        'id'        => bin2hex(random_bytes(8)),
        'room'      => $roomId,
        'sender'    => $alias,
        'avatar'    => $avatar,
        'body'      => $text,
        'file'      => $fileData,
        'timestamp' => time()
    ];

    if (class_exists('StorageRepository') && method_exists('StorageRepository', 'append')) {
        StorageRepository::append($roomId, $out);
    } elseif (class_exists('Storage') && method_exists('Storage', 'append')) {
        Storage::append($roomId, $out);
    }

    respond_json(['status' => 'ok', 'data' => $out]);
}

respond_json(['error' => 'Unknown action'], 400);
