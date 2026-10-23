<?php /** @var array $old @var ?string $error */ ?>
<?php
$asideTitle = 'Quay lại lịch tư vấn của bạn';
$asideText = 'Đăng nhập để xem khung giờ còn trống, theo dõi yêu cầu đã gửi và phản hồi sau buổi gặp.';
?>
<div class="auth-shell">
    <?php require __DIR__ . '/_cotthuonghieu.php'; ?>
    <div class="auth-main">
        <div class="auth-box">
            <h1 class="h3 mb-1">Đăng nhập</h1>
            <p class="text-muted mb-4">Dùng email và mật khẩu đã đăng ký.</p>

            <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>

            <form method="post" action="<?= e(url('auth/login')) ?>" class="auth-form">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= e($old['email'] ?? '') ?>"
                           autocomplete="username" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password">Hiện</button>
                    </div>
                </div>
                <button class="btn btn-primary w-100" type="submit">Đăng nhập</button>
            </form>
            <p class="mt-3 mb-0 text-center">Chưa có tài khoản? <a href="<?= e(url('auth/register')) ?>">Đăng ký</a></p>
        </div>
    </div>
</div>
