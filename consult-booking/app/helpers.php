<?php
declare(strict_types=1);

/** Đọc cấu hình, hỗ trợ khóa dạng "db.host". Ưu tiên config.local.php, nếu chưa có thì dùng config.example.php. */
function config(string $key, mixed $default = null): mixed
{
    static $cfg = null;
    if ($cfg === null) {
        $dir = APP_PATH . '/config';
        $cfg = require is_file("$dir/config.local.php") ? "$dir/config.local.php" : "$dir/config.example.php";
    }
    $value = $cfg;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/** Mã hóa output (chống XSS). Mọi dữ liệu người dùng in ra HTML đều phải đi qua hàm này. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Thư mục gốc của public/ tính từ URL (chạy được cả ở gốc domain lẫn thư mục con như /consult-booking/public). */
function base_path(): string
{
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
}

function url(string $route = '', array $params = []): string
{
    $query = ($route === '' ? [] : ['r' => $route]) + $params;
    return base_path() . '/index.php' . ($query ? '?' . http_build_query($query) : '');
}

function asset(string $path): string
{
    return base_path() . '/assets/' . ltrim($path, '/');
}

function redirect(string $route, array $params = []): never
{
    header('Location: ' . url($route, $params));
    exit;
}

/** Flash message: lưu vào session, hiển thị một lần ở lần tải trang kế tiếp. */
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'msg' => $message];
}

function pull_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

function csrf_field(): string
{
    return Csrf::field();
}

function abort(int $code, string $message = ''): never
{
    $titles = [
        403 => 'Bạn không có quyền truy cập',
        404 => 'Không tìm thấy trang',
        405 => 'Phương thức không được hỗ trợ',
        419 => 'Phiên làm việc không hợp lệ',
    ];
    http_response_code($code);
    echo View::render('errors/error', [
        'code' => $code, 'heading' => $titles[$code] ?? 'Đã có lỗi xảy ra', 'message' => $message, 'title' => (string) $code,
    ]);
    exit;
}

function role_label(string $role): string
{
    return ['student' => 'Sinh viên', 'lecturer' => 'Giảng viên', 'admin' => 'Quản trị viên'][$role] ?? $role;
}

/** @return array{0:string,1:string} [nhãn, màu Bootstrap] */
function status_label(string $status): array
{
    return ['active' => ['Hoạt động', 'success'], 'pending' => ['Chờ duyệt', 'warning'], 'locked' => ['Đã khóa', 'danger']][$status]
        ?? [$status, 'secondary'];
}

/** Chữ cái đầu của tên gọi (từ cuối trong họ tên theo cách gọi người Việt), dùng cho avatar. */
function initial(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $last = (string) end($parts);
    return mb_strtoupper(mb_substr($last, 0, 1)) ?: '?';
}
