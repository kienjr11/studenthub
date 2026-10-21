<?php
/** @var string $content */
$me = Auth::user();
$flashes = pull_flashes();
$route = (string) ($_GET['r'] ?? 'home/index');
$active = static fn (string $prefix): string => str_starts_with($route, $prefix) ? ' active' : '';
$alertTypes = ['success' => 'success', 'danger' => 'danger', 'warning' => 'warning', 'info' => 'info'];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title><?= !empty($title) ? e($title) . ' – ' : '' ?><?= e(config('app.name')) ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='7' fill='%23142b45'/%3E%3Crect x='6' y='6' width='9' height='9' rx='2' fill='%23fff'/%3E%3Crect x='17' y='6' width='9' height='9' rx='2' fill='%23c98a00'/%3E%3Crect x='7' y='18' width='7' height='7' rx='1.5' fill='none' stroke='%23fff' stroke-width='2'/%3E%3Crect x='17' y='17' width='9' height='9' rx='2' fill='%23fff'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(asset('css/app.css')) ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg app-nav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(url('home/index')) ?>">
            <span class="brand-mark" aria-hidden="true"><i></i><i></i><i></i><i></i></span><?= e(config('app.name')) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link<?= $active('home/') ?>" href="<?= e(url('home/index')) ?>">Trang chủ</a></li>
                <?php if ($me): ?>
                    <li class="nav-item"><a class="nav-link<?= $active('dashboard/') ?>" href="<?= e(url('dashboard/index')) ?>">Khu vực của tôi</a></li>
                <?php endif; ?>
                <?php if ($me && $me['vai_tro'] === 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle<?= $active('admin/') ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Quản trị</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= e(url('admin/dashboard')) ?>">Tổng quan</a></li>
                            <li><a class="dropdown-item" href="<?= e(url('admin/users')) ?>">Tài khoản người dùng</a></li>
                            <li><a class="dropdown-item" href="<?= e(url('admin/catalog', ['type' => 'departments'])) ?>">Danh mục đơn vị</a></li>
                            <li><a class="dropdown-item" href="<?= e(url('admin/catalog', ['type' => 'specialties'])) ?>">Danh mục chuyên môn</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if ($me): ?>
                    <span class="nav-user"><?= e($me['ho_ten']) ?> <span class="badge text-bg-light"><?= e(role_label($me['vai_tro'])) ?></span></span>
                    <form method="post" action="<?= e(url('auth/logout')) ?>" class="m-0">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-outline-light" type="submit">Đăng xuất</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-light" href="<?= e(url('auth/login')) ?>">Đăng nhập</a>
                    <a class="btn btn-sm btn-light" href="<?= e(url('auth/register')) ?>">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if ($flashes): ?>
    <div class="container mt-3">
        <?php foreach ($flashes as $f): ?>
            <div class="alert alert-<?= e($alertTypes[$f['type']] ?? 'info') ?> alert-dismissible fade show" role="alert">
                <?= e($f['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main class="flex-grow-1"><?= $content ?></main>

<footer class="app-footer py-3">
    <div class="container small">Bài tập lớn Lập trình Web: PHP 8, MySQL, PDO, kiến trúc MVC.</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
