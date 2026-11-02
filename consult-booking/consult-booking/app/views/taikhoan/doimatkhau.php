<?php
/** @var array $loi */
$tabHienTai = 'doi-mat-khau';
$cls = static fn (string $f): string => isset($loi[$f]) ? ' is-invalid' : '';
$fb  = static fn (string $f): string => isset($loi[$f]) ? '<div class="invalid-feedback d-block">' . e($loi[$f]) . '</div>' : '';
$min = (int) config('security.min_password_length', 8);
?>
<div class="container py-4">
    <h1 class="h3 mb-3 page-title">Tài khoản của tôi</h1>
    <?php require __DIR__ . '/_thanhtab.php'; ?>

    <div class="row">
        <div class="col-md-9 col-lg-6">
            <form method="post" action="<?= e(url('tai-khoan/doi-mat-khau')) ?>" class="auth-form" novalidate>
                <?= csrf_field() ?>
                <h2 class="h5 mb-3">Đổi mật khẩu</h2>

                <div class="mb-3">
                    <label for="mat_khau_hien_tai" class="form-label">Mật khẩu hiện tại</label>
                    <div class="input-group">
                        <input type="password" class="form-control<?= $cls('mat_khau_hien_tai') ?>" id="mat_khau_hien_tai" name="mat_khau_hien_tai" autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button" data-toggle-password="#mat_khau_hien_tai">Hiện</button>
                    </div>
                    <?= $fb('mat_khau_hien_tai') ?>
                </div>

                <div class="mb-3">
                    <label for="mat_khau_moi" class="form-label">Mật khẩu mới</label>
                    <div class="input-group">
                        <input type="password" class="form-control<?= $cls('mat_khau_moi') ?>" id="mat_khau_moi" name="mat_khau_moi" autocomplete="new-password" minlength="<?= e($min) ?>" required>
                        <button class="btn btn-outline-secondary" type="button" data-toggle-password="#mat_khau_moi">Hiện</button>
                    </div>
                    <div class="form-text">Ít nhất <?= e($min) ?> ký tự, gồm cả chữ và số, khác mật khẩu hiện tại.</div>
                    <?= $fb('mat_khau_moi') ?>
                </div>

                <div class="mb-3">
                    <label for="nhap_lai_mat_khau_moi" class="form-label">Nhập lại mật khẩu mới</label>
                    <div class="input-group">
                        <input type="password" class="form-control<?= $cls('nhap_lai_mat_khau_moi') ?>" id="nhap_lai_mat_khau_moi" name="nhap_lai_mat_khau_moi" autocomplete="new-password" required>
                        <button class="btn btn-outline-secondary" type="button" data-toggle-password="#nhap_lai_mat_khau_moi">Hiện</button>
                    </div>
                    <?= $fb('nhap_lai_mat_khau_moi') ?>
                </div>

                <p class="text-muted small">Sau khi đổi, các thiết bị khác đang đăng nhập tài khoản này sẽ bị đăng xuất.</p>
                <button class="btn btn-primary" type="submit">Đổi mật khẩu</button>
            </form>
        </div>
    </div>
</div>
