<?php
declare(strict_types=1);

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        ini_set('session.use_strict_mode', '1');   // từ chối session id do client tự đặt
        ini_set('session.use_only_cookies', '1');  // không nhận session id qua URL
        session_name((string) config('session.name', 'CB_SESSID'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $https,
            'httponly' => true,                    // JavaScript không đọc được cookie
            'samesite' => 'Lax',                   // giảm nguy cơ CSRF qua điều hướng chéo site
        ]);
        session_start();

        $now = time();
        $idle = (int) config('session.idle_timeout', 1800);
        if (isset($_SESSION['_last']) && $now - (int) $_SESSION['_last'] > $idle) {
            self::reset();
            flash('warning', 'Phiên làm việc đã hết hạn, vui lòng đăng nhập lại.');
        }
        $_SESSION['_last'] = $now;
    }

    /** Đổi session id (chống session fixation) và giữ lại dữ liệu hiện có. */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /** Xóa toàn bộ dữ liệu phiên và cấp session id mới (dùng khi đăng xuất / hết hạn). */
    public static function reset(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }
}
