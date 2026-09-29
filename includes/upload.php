<?php
declare(strict_types=1);

if (!defined('INIT_CHECK')) {
    http_response_code(403);
    exit;
}

final class UploadHandler
{
    public static function process(array $file): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Invalid upload parameters');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('File size limit exceeded');
            default:
                throw new RuntimeException('Upload processing failed');
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            throw new RuntimeException('File exceeds max size limit');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, ALLOWED_MIMES)) {
            throw new RuntimeException('Unsupported mime type');
        }

        $ext = ALLOWED_MIMES[$mime];
        $hashName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPath = DIR_UPLOADS . $hashName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Storage operation failed');
        }

        chmod($targetPath, 0644);

        return [
            'hash' => $hashName,
            'original' => sanitize_str(basename($file['name']), 64),
            'mime' => $mime,
            'size' => $file['size']
        ];
    }
}
