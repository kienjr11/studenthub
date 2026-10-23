<?php
/** @var array $me @var array $profile @var array $completeness @var array $stats @var array $upcoming */
$tiles = [
    ['Chờ duyệt', $stats['cho_duyet'], 'giang-vien/lich-hen', ['trang_thai' => 'cho_duyet'], $stats['cho_duyet'] > 0 ? 'text-warning' : ''],
    ['Lịch hẹn hôm nay', $stats['hom_nay'], 'giang-vien/lich-hen', ['trang_thai' => 'da_duyet'], ''],
    ['Khung giờ sắp tới', $stats['slot_sap_toi'], 'giang-vien/lich-hen', [], ''],
    ['Đã hoàn thành', $stats['hoan_thanh'], 'giang-vien/lich-hen', ['trang_thai' => 'hoan_thanh'], ''],
];
?>
<div class="container py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <span class="avatar avatar-lg avatar-giang_vien" aria-hidden="true"><?= e(initial($me['ho_ten'])) ?></span>
        <div class="flex-grow-1">
            <h1 class="h3 mb-0">Xin chào, <?= e(lecturer_title($profile)) ?></h1>
            <p class="text-muted mb-0"><?= e($profile['ten_don_vi'] ?? 'Chưa gán đơn vị') ?></p>
        </div>
        <a class="btn btn-outline-primary d-none d-md-inline-block" href="<?= e(url('giang-vien/ho-so')) ?>">Chỉnh sửa hồ sơ</a>
    </div>

    <?php if ($completeness['percent'] < 100): ?>
        <div class="role-block role-lecturer mb-4">
            <div class="d-flex justify-content-between mb-1"><strong>Hồ sơ đã hoàn thiện <?= e($completeness['percent']) ?>%</strong>
                <a href="<?= e(url('giang-vien/ho-so')) ?>">Bổ sung ngay</a></div>
            <div class="progress mb-2" role="progressbar" aria-valuenow="<?= e($completeness['percent']) ?>" aria-valuemin="0" aria-valuemax="100" style="height: .5rem">
                <div class="progress-bar" style="width: <?= e($completeness['percent']) ?>%"></div>
            </div>
            <span class="small text-muted">Còn thiếu: <?= e(implode(', ', $completeness['missing'])) ?>.</span>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <?php foreach ($tiles as [$label, $value, $route, $params, $cls]): ?>
            <div class="col-6 col-lg-3">
                <a class="role-block d-block text-decoration-none" href="<?= e(url($route, $params)) ?>">
                    <div class="small text-muted"><?= e($label) ?></div>
                    <div class="display-6 fw-bold <?= e($cls) ?>"><?= e($value) ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <h2 class="h5 section-title">Cuộc hẹn sắp tới</h2>
            <?php if (!$upcoming): ?>
                <p class="text-muted">Chưa có cuộc hẹn nào sắp tới.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle app-table">
                        <thead><tr><th>Thời gian</th><th>Sinh viên</th><th>Chủ đề</th><th>Trạng thái</th></tr></thead>
                        <tbody>
                        <?php foreach ($upcoming as $a): ?>
                            <tr>
                                <td class="small"><?= e(date('d/m H:i', strtotime($a['bat_dau']))) ?>–<?= e(date('H:i', strtotime($a['ket_thuc']))) ?></td>
                                <td><?= e($a['ten_sinh_vien']) ?></td>
                                <td><?= e($a['chu_de']) ?></td>
                                <td><span class="badge text-bg-<?= $a['trang_thai'] === 'da_duyet' ? 'success' : 'warning' ?>"><?= $a['trang_thai'] === 'da_duyet' ? 'Đã duyệt' : 'Chờ duyệt' ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <a href="<?= e(url('giang-vien/lich-hen')) ?>">Xem tất cả lịch hẹn &rarr;</a>
        </div>
        <div class="col-lg-4">
            <h2 class="h5 section-title">Đánh giá từ sinh viên</h2>
            <?php if ($stats['so_danh_gia'] === 0): ?>
                <p class="text-muted">Chưa có đánh giá nào.</p>
            <?php else: ?>
                <div class="display-6 fw-bold"><?= e(number_format($stats['diem_tb'], 1)) ?> <span class="fs-5 text-warning">★</span></div>
                <div class="text-muted small">Trung bình từ <?= e($stats['so_danh_gia']) ?> đánh giá</div>
            <?php endif; ?>
            <h2 class="h5 section-title mt-4">Lĩnh vực tư vấn</h2>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($profile['chuyen_mon'] as $cm): ?><span class="pill"><?= e($cm['ten_chuyen_mon']) ?></span><?php endforeach; ?>
                <?php if (!$profile['chuyen_mon']): ?><span class="text-muted">Chưa chọn lĩnh vực nào.</span><?php endif; ?>
            </div>
        </div>
    </div>
</div>