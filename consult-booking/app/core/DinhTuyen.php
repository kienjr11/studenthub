<?php
declare(strict_types=1);

/** Bộ định tuyến: đọc bảng tuyến đường (tuyenduong.php), khớp "phương thức + r=nhóm/hành-động" rồi gọi controller tương ứng. */
final class DinhTuyen
{
    public function __construct(private array $routes)
    {
    }

    public function dispatch(): void
    {
        $verb = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $method = match ($verb) {
            'GET', 'HEAD' => 'GET',
            'POST'        => 'POST',
            default       => abort(405),
        };
        $route = (string) ($_GET['r'] ?? 'home/index');

        foreach ($this->routes as [$m, $path, $class, $action]) {
            if ($m === $method && $path === $route) {
                if ($method === 'POST') {
                    ChongCsrf::check();   // kiểm tra CSRF tập trung cho MỌI route POST, không controller nào quên được
                }
                (new $class())->$action();
                return;
            }
        }
        abort(404, 'Đường dẫn bạn truy cập không tồn tại.');
    }
}
