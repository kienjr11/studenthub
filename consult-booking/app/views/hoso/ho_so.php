<?php
/** @var array $profile @var array $completeness */
$hocHam = HoSoGiangVien::HOC_HAM[$profile['hoc_ham'] ?? ''] ?? null;
$hocVi  = HoSoGiangVien::HOC_VI[$profile['hoc_vi'] ?? ''] ?? null;
$chuaCo = '<span class="text-muted">Chưa cập nhật</span>';
?>
<div class="container py-4" style="max-width: 860px">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
        <div class="d-flex align-items-center gap-3">
            <?= lecturer_avatar($profile, 'avatar-xl') ?>
            <div>
                <h1 class="h3 mb-1 page-title"><?= e(lecturer_title($profile)) ?></h1>
                <div class="text-muted"><?= e($profile['ten_don_vi'] ?? 'Chưa gán đơn vị') ?></div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('giang-vien/chi-tiet', ['id' => $profile['ma_nguoi_dung']])) ?>">Xem như sinh viên</a>
            <a class="btn btn-primary btn-sm" href="<?= e(url('giang-vien/ho-so/sua')) ?>">Chỉnh sửa hồ sơ</a>
        </div>
    </div>

    <?php if ($completeness['percent'] < 100): ?>
        <div class="role-block role-lecturer mb-4">
            <div class="d-flex justify-content-between mb-1">
                <strong>Hồ sơ đã hoàn thiện <?= e($completeness['percent']) ?>%</strong>
                <a href="<?= e(url('giang-vien/ho-so/sua')) ?>">Bổ sung ngay</a>
            </div>
            <div class="progress mb-2" role="progressbar" aria-valuenow="<?= e($completeness['percent']) ?>" aria-valuemin="0" aria-valuemax="100" style="height: .5rem">
                <div class="progress-bar" style="width: <?= e($completeness['percent']) ?>%"></div>
            </div>
            <span class="small text-muted">Còn thiếu: <?= e(implode(', ', $completeness['missing'])) ?>.</span>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-5">
            <h2 class="h5">Thông tin cá nhân</h2>
            <dl class="info-list">
                <div><dt>Họ và tên</dt><dd><?= e($profile['ho_ten']) ?></dd></div>
                <div><dt>Học hàm</dt><dd><?= $hocHam ? e($hocHam) : $chuaCo ?></dd></div>
                <div><dt>Học vị</dt><dd><?= $hocVi ? e($hocVi) : $chuaCo ?></dd></div>
                <div><dt>Khoa / đơn vị</dt><dd><?= !empty($profile['ten_don_vi']) ? e($profile['ten_don_vi']) : $chuaCo ?></dd></div>
                <div><dt>Email</dt><dd><?= e($profile['email']) ?></dd></div>
                <div><dt>Điện thoại</dt><dd><?= !empty($profile['so_dien_thoai']) ? e($profile['so_dien_thoai']) : $chuaCo ?></dd></div>
                <div><dt>Phòng làm việc</dt><dd><?= !empty($profile['phong_lam_viec']) ? e($profile['phong_lam_viec']) : $chuaCo ?></dd></div>
            </dl>
        </div>
        <div class="col-md-7">
            <h2 class="h5">Giới thiệu</h2>
            <p><?= !empty($profile['gioi_thieu']) ? nl2br(e($profile['gioi_thieu'])) : '<span class="text-muted">Bạn chưa viết phần giới thiệu.</span>' ?></p>
            <h2 class="h5 mt-4">Lĩnh vực tư vấn</h2>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($profile['chuyen_mon'] as $cm): ?><span class="pill"><?= e($cm['ten_chuyen_mon']) ?></span><?php endforeach; ?>
                <?php if (!$profile['chuyen_mon']): ?><span class="text-muted">Chưa chọn lĩnh vực nào.</span><?php endif; ?>
            </div>
        </div>
    </div>
</div>