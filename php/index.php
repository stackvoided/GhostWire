<?php
declare(strict_types=1);

define('INIT_CHECK', true);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/upload.php';

SecurityConfig::applyHeaders();
$token = SecurityConfig::generateCsrfToken();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityConfig::verifyCsrfToken($_POST['csrf_token'] ?? '')) {$error = 'Сессия устарела. Перезагрузите страницу.';
    } else {
        $alias = sanitize_str($_POST['alias'] ?? '', 32);
        $key   =$_POST['room_key'] ?? '';

        if (empty($alias) || mb_strlen($alias, 'UTF-8') < 2) {$error = 'Укажите псевдоним от 2 символов.';
        } elseif (!validate_key($key)) {$error = 'Ключ должен быть от 10 до 30 символов.';
        } else {
            $avatarHash =$_POST['cached_avatar'] ?? '';

            if (isset($_FILES['avatar']) &&$_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                try {
                    $up = UploadHandler::process($_FILES['avatar']);
                    $avatarHash =$up['hash'];
                } catch (Exception $e) {
                    $error =$e->getMessage();
                }
            }

            if ($error === null) {$opts = [
                    'expires'  => time() + COOKIE_LIFETIME,
                    'path'     => '/',
                    'secure'   => isset($_SERVER['HTTPS']),
                    'httponly' => false,
                    'samesite' => 'Strict'
                ];
                setcookie('gw_alias', $alias,$opts);
                setcookie('gw_avatar', $avatarHash,$opts);

                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                $_SESSION['active_key'] =$key;

                header('Location: chat.php', true, 303);
                exit;
            }
        }
    }
}

$savedAlias  =$_COOKIE['gw_alias'] ?? '';
$savedAvatar =$_COOKIE['gw_avatar'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= APP_NAME ?> — Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="viewport-center">
    <main class="portal-frame">
        <header class="portal-header">
            <h1><?= APP_NAME ?></h1>
            <p class="status-tag">E2E Session Isolation</p>
        </header>

        <?php if ($error): ?>
            <div class="system-alert error"><span><?= htmlspecialchars($error) ?></span></div>
        <?php endif; ?>

        <form action="index.php" method="POST" enctype="multipart/form-data" class="portal-form">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <input type="hidden" name="cached_avatar" value="<?= htmlspecialchars($savedAvatar) ?>">

            <div class="input-block">
                <label for="alias">Псевдоним</label>
                <input type="text" id="alias" name="alias" required autocomplete="off" maxlength="32" value="<?= htmlspecialchars($savedAlias) ?>" placeholder="Ghost_99">
            </div>

            <div class="input-block">
                <label>Аватар профиля</label>
                <div class="avatar-uploader">
                    <div class="avatar-preview-box" id="avatarPreview">
                        <?php if ($savedAvatar && file_exists(DIR_UPLOADS .$savedAvatar)): ?>
                            <img src="file.php?name=<?= htmlspecialchars($savedAvatar) ?>" alt="Avatar">
                        <?php else: ?>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <?php endif; ?>
                    </div>
                    <label for="avatar" class="file-label-btn">Загрузить</label>
                    <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif">
                </div>
            </div>

            <div class="input-block">
                <label for="room_key">Ключ доступа (10–30 символов)</label>
                <div class="key-input-wrapper">
                    <input type="password" id="room_key" name="room_key" required minlength="10" maxlength="30" placeholder="••••••••••••••••" autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="submit-btn">Подключиться</button>
        </form>
    </main>

    <script>
        document.getElementById('avatar')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    const box = document.getElementById('avatarPreview');
                    box.innerHTML = `<img src="${evt.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
