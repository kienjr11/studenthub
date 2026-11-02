<?php
/** @var array $toi @var array $cu @var array $loi @var array $dsChuyenMon @var int[] $daChon */
$tabHienTai = 'ho-so';
$laGiangVien = $toi['vai_tro'] === 'giang_vien';
$hoTen = $cu['ho_ten'] ?? $toi['ho_ten'];
?>
<div class="container py-4">
    <h1 class="h3 mb-3 page-title">Tài khoản của tôi</h1>
    <?php require __DIR__ . '/_thanhtab.php'; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="avatar avatar-lg avatar-<?= e($toi['vai_tro']) ?>" aria-hidden="true"><?= e(initial($toi['ho_ten'])) ?></span>
                <div>
                    <h2 class="h5 mb-0"><?= e($toi['ho_ten']) ?></h2>
                    <span class="text-muted"><?= e(role_label($toi['vai_tro'])) ?></span>
                </div>
            </div>
            <dl class="info-list">
                <div><dt>Email đăng nhập</dt><dd><?= e($toi['email']) ?></dd></div>
                <?php if ($laGiangVien): ?>
                    <div><dt>Đơn vị</dt><dd><?= e($toi['ten_don_vi'] ?? 'Chưa gán đơn vị') ?></dd></div>
                <?php endif; ?>
                <div><dt>Ngày tạo tài khoản</dt><dd><?= e(date('d/m/Y', strtotime($toi['ngay_tao']))) ?></dd></div>
                <div><dt>Đăng nhập gần nhất</dt><dd><?= $toi['dang_nhap_lan_cuoi'] ? e(date('d/m/Y H:i', strtotime($toi['dang_nhap_lan_cuoi']))) : 'Lần đầu' ?></dd></div>
            </dl>
        </div>

        <div class="col-lg-7">
            <form method="post" action="<?= e(url('tai-khoan/ho-so/cap-nhat')) ?>" class="auth-form mb-4" novalidate>
                <?= csrf_field() ?>
                <h2 class="h5 mb-3">Chỉnh sửa hồ sơ</h2>
                <div class="mb-3">
                    <label for="ho_ten" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control<?= isset($loi['ho_ten']) ? ' is-invalid' : '' ?>" id="ho_ten" name="ho_ten"
                           value="<?= e($hoTen) ?>" maxlength="100" autocomplete="name" required>
                    <?php if (isset($loi['ho_ten'])): ?><div class="invalid-feedback d-block"><?= e($loi['ho_ten']) ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" value="<?= e($toi['email']) ?>" disabled aria-describedby="email-ghi-chu">
                    <div id="email-ghi-chu" class="form-text">Email dùng để đăng nhập nên chưa thể tự đổi. Nếu cần đổi, hãy liên hệ quản trị viên.</div>
                </div>
                <button class="btn btn-primary" type="submit">Lưu thay đổi</button>
            </form>

            <?php if ($laGiangVien): ?>
                <form method="post" action="<?= e(url('tai-khoan/chuyen-mon')) ?>" class="auth-form">
                    <?= csrf_field() ?>
                    <h2 class="h5 mb-1">Lĩnh vực tư vấn</h2>
                    <p class="text-muted mb-3">Sinh viên tìm giảng viên theo các lĩnh vực này. Danh mục do quản trị viên quản lý.</p>
                    <?php if (!$dsChuyenMon): ?>
                        <p class="text-muted">Quản trị viên chưa tạo chuyên môn nào.</p>
                    <?php else: ?>
                        <div class="chuyen-mon-luoi mb-3">
                            <?php foreach ($dsChuyenMon as $cm): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="chuyen_mon[]" value="<?= e($cm['ma']) ?>" id="cm-<?= e($cm['ma']) ?>"
                                           <?= in_array((int) $cm['ma'], $daChon, true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="cm-<?= e($cm['ma']) ?>"><?= e($cm['ten']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn btn-primary" type="submit">Lưu lĩnh vực tư vấn</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
