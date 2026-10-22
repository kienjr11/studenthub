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
        // Phiên gắn với "dấu vân tay" của mật khẩu hiện tại: đổi mật khẩu thì mọi phiên cũ (thiết bị khác) tự vô hiệu.
        $khop = $found !== null && hash_equals((string) ($_SESSION['dvt'] ?? ''), (string) $found['dau_van_tay_mk']);
        if ($found === null || $found['trang_thai'] !== 'hoat_dong' || !$khop) {
            unset($_SESSION['uid'], $_SESSION['dvt']);
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
        $_SESSION['dvt'] = self::dauVanTay((string) $user['mat_khau_bam']);
    }

    /** 16 ký tự đầu của SHA-256 chuỗi băm mật khẩu (khớp với LEFT(SHA2(mat_khau_bam, 256), 16) trong NguoiDung::find). */
    public static function dauVanTay(string $matKhauBam): string
    {
        return substr(hash('sha256', $matKhauBam), 0, 16);
    }

    /** Gọi ngay sau khi đổi mật khẩu: đổi session id và cập nhật dấu vân tay để phiên hiện tại vẫn dùng được. */
    public static function lamMoiPhien(string $matKhauBamMoi): void
    {
        PhienLamViec::regenerate();
        $_SESSION['dvt'] = self::dauVanTay($matKhauBamMoi);
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
