<?php
declare(strict_types=1);

/** Xác thực + phân quyền. Controller chỉ cần gọi XacThuc::requireLogin() hoặc XacThuc::requireRole(...). */
final class XacThuc
{
    /** Người dùng hiện tại, luôn đọc lại từ CSDL để việc khóa tài khoản/đổi vai trò có hiệu lực ngay. */
    public static function user(): ?array
    {
        static $loaded = false, $user = null;
        if ($loaded) {
            return $user;
        }
        $loaded = true;
        $id = $_SESSION['uid'] ?? null;
        if ($id === null) {
            return null;
        }
        $found = (new NguoiDung())->find((int) $id);
        if ($found === null || $found['trang_thai'] !== 'hoat_dong') {
            unset($_SESSION['uid']);
            return null;
        }
        return $user = $found;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u['ma_nguoi_dung'] : null;
    }

    public static function login(array $user): void
    {
        PhienLamViec::regenerate();            // chống session fixation
        unset($_SESSION['_csrf']);        // cấp CSRF token mới cho phiên đăng nhập
        $_SESSION['uid'] = (int) $user['ma_nguoi_dung'];
    }

    public static function logout(): void
    {
        PhienLamViec::reset();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('warning', 'Vui lòng đăng nhập để tiếp tục.');
            redirect('auth/login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array(self::user()['vai_tro'], $roles, true)) {
            abort(403, 'Tài khoản của bạn không được phép truy cập chức năng này.');
        }
    }
}
