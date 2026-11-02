<?php
declare(strict_types=1);

/**
 * Front controller: mọi request đi qua file này.
 * Luồng: index.php -> DinhTuyen (tuyenduong.php) -> DieuKhien* (controller) -> MoHinh/NguoiDung/DanhMuc (model) -> view (app/views)
 */
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

require APP_PATH . '/hamtro.php';

spl_autoload_register(static function (string $class): void {
    foreach (['core', 'models', 'controllers'] as $dir) {
        $file = APP_PATH . "/$dir/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

date_default_timezone_set((string) config('app.timezone', 'Asia/Ho_Chi_Minh'));

// Bật hiển thị lỗi trực tiếp trên màn hình để dễ dàng debug khi gặp sự cố
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Header bảo mật cơ bản. CSP chỉ cho phép script/style từ chính site + CDN Bootstrap + Google Fonts.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; "
    . "style-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com; "
    . "font-src https://fonts.gstatic.com; img-src 'self' data:; "
    . "frame-ancestors 'none'; form-action 'self'; base-uri 'self'");

set_exception_handler(static function (Throwable $e): void {
    error_log((string) $e);
    http_response_code(500);
    $msg = $e->getMessage(); // Luôn hiển thị chi tiết thông điệp lỗi để debug nhanh chóng
    try {
        echo GiaoDien::render('loi/loi', ['code' => 500, 'heading' => 'Lỗi hệ thống', 'message' => $msg, 'title' => '500'], 'trong');
    } catch (Throwable) {
        echo e($msg);
    }
});

PhienLamViec::start();
(new DinhTuyen(require APP_PATH . '/tuyenduong.php'))->dispatch();