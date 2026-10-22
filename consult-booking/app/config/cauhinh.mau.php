<?php
/**
 * Cấu hình mẫu (được commit lên Git).
 * cauhinh.local.php đã nằm trong .gitignore nên mật khẩu CSDL không bị đẩy lên repo.
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
        'name'    => 'dat_lich_tu_van',
        'user'    => 'root',
        'pass'    => '',                   
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
