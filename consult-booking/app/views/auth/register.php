<?php
/** @var array $old @var array $errors @var array $departments */
$val = static fn (string $f, string $d = ''): string => e($old[$f] ?? $d);
$cls = static fn (string $f): string => isset($errors[$f]) ? ' is-invalid' : '';
$fb  = static fn (string $f): string => isset($errors[$f]) ? '<div class="invalid-feedback d-block">' . e($errors[$f]) . '</div>' : '';
$role = $old['role'] ?? 'student';
?>
<?php
$asideTitle = 'Một tài khoản, đúng vai trò của bạn';
$asideText = 'Sinh viên dùng được ngay sau khi đăng ký. Giảng viên được quản trị viên phê duyệt trước khi nhận lịch.';
?>
<div class="auth-shell">
    <?php require __DIR__ . '/_aside.php'; ?>
    <div class="auth-main">
        <div class="auth-box auth-box-wide">
            <h1 class="h3 mb-1">Tạo tài khoản</h1>
            <p class="text-muted mb-4">Chọn đúng vai trò của bạn. Quản trị viên không đăng ký công khai.</p>

            <form method="post" action="<?= e(url('auth/register')) ?>" class="auth-form" id="register-form" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <span class="form-label d-block">Bạn là</span>
                    <div class="btn-group w-100" role="group" aria-label="Vai trò">
                        <input type="radio" class="btn-check" name="role" id="role-student" value="student" <?= $role !== 'lecturer' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary" for="role-student">Sinh viên</label>
                        <input type="radio" class="btn-check" name="role" id="role-lecturer" value="lecturer" <?= $role === 'lecturer' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary" for="role-lecturer">Giảng viên</label>
                    </div>
                    <?= $fb('role') ?>
                    <div class="form-text">Tài khoản giảng viên cần quản trị viên phê duyệt trước khi đăng nhập.</div>
                </div>

                <div class="mb-3">
                    <label for="full_name" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control<?= $cls('full_name') ?>" id="full_name" name="full_name"
                           value="<?= $val('full_name') ?>" maxlength="100" autocomplete="name" required>
                    <?= $fb('full_name') ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control<?= $cls('email') ?>" id="email" name="email" value="<?= $val('email') ?>"
                           maxlength="190" autocomplete="email" required
                           data-check-email="<?= e(url('auth/check-email')) ?>">
                    <div id="email-feedback" class="form-text" aria-live="polite"></div>
                    <?= $fb('email') ?>
                </div>

                <div class="mb-3" id="dept-group">
                    <label for="department_id" class="form-label">Đơn vị công tác (dành cho giảng viên)</label>
                    <select class="form-select<?= $cls('department_id') ?>" id="department_id" name="department_id">
                        <option value="">Chọn đơn vị</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= e($d['ma']) ?>" <?= (string) ($old['department_id'] ?? '') === (string) $d['ma'] ? 'selected' : '' ?>><?= e($d['ten']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= $fb('department_id') ?>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <input type="password" class="form-control<?= $cls('password') ?>" id="password" name="password"
                               autocomplete="new-password" minlength="<?= e(config('security.min_password_length', 8)) ?>" required>
                        <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password">Hiện</button>
                    </div>
                    <div class="form-text">Ít nhất <?= e(config('security.min_password_length', 8)) ?> ký tự, gồm cả chữ và số.</div>
                    <?= $fb('password') ?>
                </div>

                <div class="mb-4">
                    <label for="password_confirm" class="form-label">Nhập lại mật khẩu</label>
                    <input type="password" class="form-control<?= $cls('password_confirm') ?>" id="password_confirm" name="password_confirm"
                           autocomplete="new-password" required>
                    <?= $fb('password_confirm') ?>
                </div>

                <button class="btn btn-primary w-100" type="submit">Tạo tài khoản</button>
            </form>
            <p class="mt-3 mb-0 text-center">Đã có tài khoản? <a href="<?= e(url('auth/login')) ?>">Đăng nhập</a></p>
        </div>
    </div>
</div>
