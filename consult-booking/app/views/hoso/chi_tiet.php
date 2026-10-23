<?php /** @var array $profile */ ?>
<div class="container py-4" style="max-width: 860px">
    <a class="small" href="<?= e(url('giang-vien/danh-sach')) ?>">&larr; Quay lại danh sách</a>
    <div class="d-flex align-items-center gap-3 my-3">
        <span class="avatar avatar-lg avatar-giang_vien" aria-hidden="true"><?= e(initial($profile['ho_ten'])) ?></span>
        <div>
            <h1 class="h3 mb-0"><?= e(lecturer_title($profile)) ?></h1>
            <div class="text-muted"><?= e($profile['ten_don_vi'] ?? 'Chưa gán đơn vị') ?></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <h2 class="h5">Thông tin liên hệ</h2>
            <dl class="info-list">
                <div><dt>Email</dt><dd><?= e($profile['email']) ?></dd></div>
                <div><dt>Điện thoại</dt><dd><?= e($profile['so_dien_thoai'] ?? 'Chưa cập nhật') ?></dd></div>
                <div><dt>Phòng làm việc</dt><dd><?= e($profile['phong_lam_viec'] ?? 'Chưa cập nhật') ?></dd></div>
            </dl>
        </div>
        <div class="col-md-7">
            <h2 class="h5">Giới thiệu</h2>
            <p><?= $profile['gioi_thieu'] ? nl2br(e($profile['gioi_thieu'])) : '<span class="text-muted">Giảng viên chưa viết phần giới thiệu.</span>' ?></p>
            <h2 class="h5 mt-4">Lĩnh vực tư vấn</h2>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($profile['chuyen_mon'] as $cm): ?><span class="pill"><?= e($cm['ten_chuyen_mon']) ?></span><?php endforeach; ?>
                <?php if (!$profile['chuyen_mon']): ?><span class="text-muted">Chưa cập nhật.</span><?php endif; ?>
            </div>
            <?php /* Module đặt lịch (Dương) gắn nút "Đặt lịch" tại đây, truyền ?giang_vien=<?= $profile['ma_nguoi_dung'] ?> */ ?>
        </div>
    </div>
</div>