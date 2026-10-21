<?php
declare(strict_types=1);

/** Chống CSRF bằng token ngẫu nhiên gắn với session. Router tự kiểm tra mọi request POST. */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function check(): void
    {
        // Form gửi qua trường _csrf; Fetch API gửi qua header X-CSRF-Token.
        $sent = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!is_string($sent) || !hash_equals(self::token(), $sent)) {
            abort(419, 'Phiên làm việc đã hết hạn hoặc yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.');
        }
    }
}
