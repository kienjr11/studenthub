<?php
/** @var array $me @var array $profile @var array $completeness @var array $stats @var array $upcoming */

// 3 ô thống kê cốt lõi cho giảng viên
$tiles = [
    [
        'label'  => 'Cần duyệt', 
        'value'  => $stats['cho_duyet'] ?? 0, 
        'route'  => 'giang-vien/lich-hen', 
        'params' => ['trang_thai' => 'cho_duyet'], 
        'cls'    => ($stats['cho_duyet'] ?? 0) > 0 ? 'text-warning' : ''
    ],
    [
        'label'  => 'Lịch hẹn hôm nay', 
        'value'  => $stats['hom_nay'] ?? 0, 
        'route'  => 'giang-vien/lich-hen', 
        'params' => ['ngay' => date('Y-m-d'), 'trang_thai' => 'da_duyet'], 
        'cls'    => 'text-primary'
    ],
    [
        'label'  => 'Đã hoàn thành', 
        'value'  => $stats['hoan_thanh'] ?? 0, 
        'route'  => 'giang-vien/lich-hen', 
        'params' => ['trang_thai' => 'hoan_thanh'], 
        'cls'    => 'text-muted'
    ],
];
?>
<div class="container py-4">
    <!-- Header thông tin giảng viên -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <?= lecturer_avatar($profile + ['ho_ten' => $me['ho_ten']], 'avatar-lg') ?>
        <div class="flex-grow-1">
            <h1 class="h3 mb-0">Xin chào, <?= e(lecturer_title($profile)) ?></h1>
            <p class="text-muted mb-0"><?= e($profile['ten_don_vi'] ?? 'Chưa gán đơn vị') ?></p>
        </div>
        <a class="btn btn-outline-primary d-none d-md-inline-block" href="<?= e(url('giang-vien/ho-so')) ?>">Xem hồ sơ</a>
    </div>

    <!-- Thanh tiến độ hoàn thiện hồ sơ -->
    <?php if (($completeness['percent'] ?? 100) < 100): ?>
        <div class="role-block role-lecturer mb-4">
            <div class="d-flex justify-content-between mb-1">
                <strong>Hồ sơ đã hoàn thiện <?= e($completeness['percent']) ?>%</strong>
                <a href="<?= e(url('giang-vien/ho-so')) ?>">Bổ sung ngay</a>
            </div>
            <div class="progress mb-2" role="progressbar" aria-valuenow="<?= e($completeness['percent']) ?>" aria-valuemin="0" aria-valuemax="100" style="height: .5rem">
                <div class="progress-bar" style="width: <?= e($completeness['percent']) ?>%"></div>
            </div>
            <span class="small text-muted">Còn thiếu: <?= e(implode(', ', $completeness['missing'] ?? [])) ?>.</span>
        </div>
    <?php endif; ?>

    <!-- 3 thẻ thống kê (Tiles) chia đều 3 cột -->
    <div class="row g-3 mb-4">
        <?php foreach ($tiles as $tile): ?>
            <div class="col-12 col-md-4">
                <a class="role-block d-block text-decoration-none shadow-sm h-100 p-3" href="<?= e(url($tile['route'], $tile['params'])) ?>">
                    <div class="small text-muted mb-1"><?= e($tile['label']) ?></div>
                    <div class="display-6 fw-bold <?= e($tile['cls']) ?>"><?= e($tile['value']) ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Danh sách cuộc hẹn sắp tới & Đánh giá/Chuyên môn -->
    <div class="row g-4">
        <div class="col-lg-8">
            <h2 class="h5 section-title">Cuộc hẹn sắp tới</h2>
            <?php if (empty($upcoming)): ?>
                <p class="text-muted">Chưa có cuộc hẹn nào sắp tới.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle app-table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Sinh viên</th>
                                <th>Chủ đề</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($upcoming as $a): ?>
                            <tr>
                                <td class="small"><?= e(date('d/m H:i', strtotime($a['bat_dau']))) ?>–<?= e(date('H:i', strtotime($a['ket_thuc']))) ?></td>
                                <td><?= e($a['ten_sinh_vien'] ?? '') ?></td>
                                <td><?= e($a['chu_de'] ?? '') ?></td>
                                <td>
                                    <span class="badge text-bg-<?= ($a['trang_thai'] ?? '') === 'da_duyet' ? 'success' : 'warning' ?>">
                                        <?= ($a['trang_thai'] ?? '') === 'da_duyet' ? 'Đã duyệt' : 'Chờ duyệt' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <a href="<?= e(url('giang-vien/lich-hen')) ?>">Xem tất cả lịch hẹn →</a>
        </div>
        
        <div class="col-lg-4">
            <h2 class="h5 section-title">Đánh giá từ sinh viên</h2>
            <?php if (($stats['so_danh_gia'] ?? 0) === 0): ?>
                <p class="text-muted">Chưa có đánh giá nào.</p>
            <?php else: ?>
                <div class="display-6 fw-bold">
                    <?= e(number_format($stats['diem_tb'] ?? 0, 1)) ?> <span class="fs-5 text-warning">★</span>
                </div>
                <div class="text-muted small">Trung bình từ <?= e($stats['so_danh_gia']) ?> đánh giá</div>
            <?php endif; ?>

            <h2 class="h5 section-title mt-4">Lĩnh vực tư vấn</h2>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($profile['chuyen_mon'] ?? [] as $cm): ?>
                    <span class="pill"><?= e($cm['ten_chuyen_mon'] ?? '') ?></span>
                <?php endforeach; ?>
                <?php if (empty($profile['chuyen_mon'])): ?>
                    <span class="text-muted">Chưa chọn lĩnh vực nào.</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>