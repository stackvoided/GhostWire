<?php
declare(strict_types=1);

if (!defined('INIT_CHECK')) {
    http_response_code(403);
    exit;
}

final class StorageGarbageCollector
{
    public static function purgeExpired(): void
    {
        if (!is_dir(DIR_UPLOADS)) {
            return;
        }

        $now = time();
        $files = scandir(DIR_UPLOADS);

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = DIR_UPLOADS . $file;
            if (is_file($path)) {
                $mtime = filemtime($path);
                if ($mtime !== false && ($now - $mtime) > TTL_FILES) {
                    @unlink($path);
                }
            }
        }
    }
}
