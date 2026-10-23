<?php
/**
 * Cấu hình mẫu (được commit lên Git).
 * Cách dùng: sao chép file này thành config.local.php rồi sửa thông tin thật.
 * config.local.php đã nằm trong .gitignore nên mật khẩu CSDL không bị đẩy lên repo.
 */
return [
    'app' => [
        'name'     => 'ConsultBook',
        'debug'    => false,               // true khi đang code để xem chi tiết lỗi; luôn để false khi demo/nộp
        'timezone' => 'Asia/Ho_Chi_Minh',
    ],
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'consult_booking',
        'user'    => 'root',
        'pass'    => '',                   // XAMPP mặc định để trống
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name'         => 'CB_SESSID',
        'idle_timeout' => 1800,            // giây (30 phút không thao tác thì hết phiên)
    ],
    'security' => [
        'max_failed_logins'   => 5,        // sai quá số lần này sẽ bị khóa tạm
        'lock_minutes'        => 10,
        'min_password_length' => 8,
    ],
];
