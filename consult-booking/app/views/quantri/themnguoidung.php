<?php
/** @var array $old @var array $errors @var array $departments */
$err = static fn (string $f): string => isset($errors[$f]) ? '<div class="invalid-feedback d-block">' . e($errors[$f]) . '</div>' : '';
$bad = static fn (string $f): string => isset($errors[$f]) ? ' is-invalid' : '';
$role = $old['role'] ?? 'sinh_vien';
?>
<div class="container py-4 container-640">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <h1 class="h3 mb-0 page-title">Thêm tài khoản</h1>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('admin/users')) ?>">&larr; Quay lại danh sách</a>
    </div>
    <p class="text-muted">Tài khoản do quản trị viên tạo sẽ hoạt động ngay, không cần phê duyệt. Hãy gửi email và mật khẩu tạm cho người dùng.</p>

    <form method="post" action="<?= e(url('admin/user-store')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label d-block">Vai trò</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="role" id="role-sv" value="sinh_vien" <?= $role === 'sinh_vien' ? 'checked' : '' ?>>
                <label class="form-check-label" for="role-sv">Sinh viên</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="role" id="role-gv" value="giang_vien" <?= $role === 'giang_vien' ? 'checked' : '' ?>>
                <label class="form-check-label" for="role-gv">Giảng viên</label>
            </div>
            <?= $err('role') ?>
        </div>

        <div class="mb-3">
            <label for="full_name" class="form-label">Họ và tên</label>
            <input type="text" class="form-control<?= $bad('full_name') ?>" id="full_name" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" maxlength="100" required autofocus>
            <?= $err('full_name') ?>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control<?= $bad('email') ?>" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" maxlength="190"
                   autocomplete="off" data-check-email="<?= e(url('auth/check-email')) ?>" required>
            <div id="email-feedback" class="form-text" aria-live="polite"></div>
            <?= $err('email') ?>
        </div>

        <div class="mb-3" id="dept-group">
            <label for="department_id" class="form-label">Đơn vị công tác <span class="text-muted small">(bắt buộc với giảng viên)</span></label>
            <select class="form-select<?= $bad('department_id') ?>" id="department_id" name="department_id">
                <option value="">— Chọn đơn vị —</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= e($d['ma']) ?>" <?= (string) ($old['department_id'] ?? '') === (string) $d['ma'] ? 'selected' : '' ?>><?= e($d['ten']) ?></option>
                <?php endforeach; ?>
            </select>
            <?= $err('department_id') ?>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu tạm</label>
            <div class="input-group">
                <input type="password" class="form-control<?= $bad('password') ?>" id="password" name="password" autocomplete="new-password" required>
                <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password">Hiện</button>
            </div>
            <div class="form-text">Tối thiểu <?= e((int) config('security.min_password_length', 8)) ?> ký tự, gồm cả chữ cái và chữ số.</div>
            <?= $err('password') ?>
        </div>

        <div class="mb-4">
            <label for="password_confirm" class="form-label">Nhập lại mật khẩu</label>
            <input type="password" class="form-control<?= $bad('password_confirm') ?>" id="password_confirm" name="password_confirm" autocomplete="new-password" required>
            <?= $err('password_confirm') ?>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Tạo tài khoản</button>
            <a class="btn btn-outline-secondary" href="<?= e(url('admin/users')) ?>">Hủy</a>
        </div>
    </form>
</div>