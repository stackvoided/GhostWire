<?php
declare(strict_types=1);

define('INIT_CHECK', true);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/includes/functions.php';

SecurityConfig::applyHeaders();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$roomKey = $_SESSION['active_key'] ?? '';
$alias   = $_COOKIE['gw_alias'] ?? '';
$avatar  = $_COOKIE['gw_avatar'] ?? '';

if (empty($roomKey) || empty($alias) || !validate_key($roomKey)) {
    header('Location: index.php', true, 302);
    exit;
}

$token  = SecurityConfig::generateCsrfToken();
$roomId = derive_room_id($roomKey);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= APP_NAME ?> — Node <?= substr($roomId, 0, 8) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="chat-layout">
    <header class="chat-topbar">
        <div class="node-meta">
            <span class="status-dot"></span>
            <div>
                <div class="node-id">NODE: <?= substr($roomId, 0, 12) ?></div>
                <div class="node-sub">Шифрованный канал</div>
            </div>
        </div>
        <div class="topbar-actions">
            <div class="user-chip">
                <?php if ($avatar && file_exists(DIR_UPLOADS . $avatar)): ?>
                    <img src="file.php?name=<?= htmlspecialchars($avatar) ?>" alt="Me">
                <?php else: ?>
                    <div class="chip-fallback"><?= mb_substr(htmlspecialchars($alias), 0, 1) ?></div>
                <?php endif; ?>
                <span><?= htmlspecialchars($alias) ?></span>
            </div>
            <a href="index.php" class="leave-btn" id="exitBtn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
        </div>
    </header>

    <main class="message-feed" id="feed"></main>

    <footer class="chat-input-panel">
        <form id="dispatchForm" class="input-form">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <input type="hidden" name="action" value="dispatch">
            <input type="hidden" name="alias" value="<?= htmlspecialchars($alias) ?>">
            <input type="hidden" name="avatar_ref" value="<?= htmlspecialchars($avatar) ?>">
            <input type="hidden" name="key" value="<?= htmlspecialchars($roomKey) ?>">

            <label class="attach-btn" for="attach">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                <input type="file" id="attach" name="attachment" accept="image/*,application/pdf,text/plain">
            </label>

            <div class="field-wrapper">
                <input type="text" name="payload" id="messageField" placeholder="Зашифрованное сообщение..." autocomplete="off">
                <div id="fileIndicator" class="file-indicator"></div>
            </div>

            <button type="submit" class="send-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </form>
    </footer>

    <script>
        window.GW_CONFIG = {
            roomId: "<?= $roomId ?>",
            ttl: <?= TTL_MESSAGES ?>,
            alias: "<?= htmlspecialchars($alias) ?>"
        };
    </script>
    <script src="assets/js/crypto.js"></script>
    <script src="assets/js/storage.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
