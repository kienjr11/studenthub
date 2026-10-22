<?php
/** @var array $profile @var array $errors @var array $old @var array $departments @var array $specialties */
$err = static fn (string $f): string => isset($errors[$f]) ? '<div class="invalid-feedback d-block">' . e($errors[$f]) . '</div>' : '';
$bad = static fn (string $f): string => isset($errors[$f]) ? ' is-invalid' : '';
?>
<div class="container py-4" style="max-width: 860px">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <h1 class="h3 mb-0 page-title">Hồ sơ giảng viên</h1>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('giang-vien/chi-tiet', ['id' => $profile['ma_nguoi_dung']])) ?>">Xem như sinh viên</a>
    </div>
    <p class="text-muted">Thông tin này hiển thị cho sinh viên khi họ tìm giảng viên để đặt lịch tư vấn.</p>

    <form method="post" action="<?= e(url('giang-vien/ho-so')) ?>" novalidate>
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label" for="ho_ten">Họ và tên <span class="text-danger">*</span></label>
                <input class="form-control<?= $bad('ho_ten') ?>" id="ho_ten" name="ho_ten" maxlength="100" value="<?= e($old['ho_ten']) ?>" required>
                <?= $err('ho_ten') ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="hoc_ham">Học hàm</label>
                <select class="form-select<?= $bad('hoc_ham') ?>" id="hoc_ham" name="hoc_ham">
                    <option value="">— Không có —</option>
                    <?php foreach (HoSoGiangVien::HOC_HAM as $k => $l): ?>
                        <option value="<?= e($k) ?>" <?= $old['hoc_ham'] === $k ? 'selected' : '' ?>><?= e($l) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $err('hoc_ham') ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="hoc_vi">Học vị</label>
                <select class="form-select<?= $bad('hoc_vi') ?>" id="hoc_vi" name="hoc_vi">
                    <option value="">— Chưa cập nhật —</option>
                    <?php foreach (HoSoGiangVien::HOC_VI as $k => $l): ?>
                        <option value="<?= e($k) ?>" <?= $old['hoc_vi'] === $k ? 'selected' : '' ?>><?= e($l) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $err('hoc_vi') ?>
            </div>
            <div class="col-md-12">
                <label class="form-label" for="ma_don_vi">Khoa / đơn vị <span class="text-danger">*</span></label>
                <select class="form-select<?= $bad('ma_don_vi') ?>" id="ma_don_vi" name="ma_don_vi" required>
                    <option value="">— Chọn đơn vị —</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= e($d['ma']) ?>" <?= (string) $old['ma_don_vi'] === (string) $d['ma'] ? 'selected' : '' ?>><?= e($d['ten']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $err('ma_don_vi') ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="so_dien_thoai">Số điện thoại</label>
                <input class="form-control<?= $bad('so_dien_thoai') ?>" id="so_dien_thoai" name="so_dien_thoai" maxlength="20" value="<?= e($old['so_dien_thoai']) ?>" placeholder="0901234567">
                <?= $err('so_dien_thoai') ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="phong_lam_viec">Phòng làm việc</label>
                <input class="form-control<?= $bad('phong_lam_viec') ?>" id="phong_lam_viec" name="phong_lam_viec" maxlength="100" value="<?= e($old['phong_lam_viec']) ?>" placeholder="P.305 – Nhà A">
                <?= $err('phong_lam_viec') ?>
            </div>
            <div class="col-12">
                <label class="form-label" for="gioi_thieu">Giới thiệu bản thân</label>
                <textarea class="form-control<?= $bad('gioi_thieu') ?>" id="gioi_thieu" name="gioi_thieu" rows="4" maxlength="1000"><?= e($old['gioi_thieu']) ?></textarea>
                <div class="form-text">Tối đa 1000 ký tự.</div>
                <?= $err('gioi_thieu') ?>
            </div>
            <div class="col-12">
                <label class="form-label d-block">Lĩnh vực tư vấn <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($specialties as $s): $checked = in_array((int) $s['ma'], $old['chuyen_mon'], true); ?>
                        <div>
                            <input type="checkbox" class="btn-check" id="cm<?= e($s['ma']) ?>" name="chuyen_mon[]" value="<?= e($s['ma']) ?>" <?= $checked ? 'checked' : '' ?>>
                            <label class="btn btn-sm btn-outline-primary" for="cm<?= e($s['ma']) ?>" title="<?= e($s['mo_ta'] ?? '') ?>"><?= e($s['ten']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?= $err('chuyen_mon') ?>
                <div class="form-text">Danh mục chuyên môn do quản trị viên quản lý.</div>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Lưu hồ sơ</button>
            <a class="btn btn-outline-secondary" href="<?= e(url('dashboard/lecturer')) ?>">Về bảng điều khiển</a>
        </div>
    </form>
</div>