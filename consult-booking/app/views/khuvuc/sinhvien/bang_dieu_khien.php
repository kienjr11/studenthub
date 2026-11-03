<?php
/** @var array $me @var array $stats @var array $upcoming */

$tiles = [
    ['label' => 'Chờ giảng viên duyệt', 'value' => $stats['cho_duyet'],     'params' => ['trang_thai' => 'cho_duyet'],  'cls' => $stats['cho_duyet'] > 0 ? 'text-warning' : ''],
    ['label' => 'Lịch hẹn sắp tới',     'value' => $stats['sap_toi'],       'params' => ['trang_thai' => 'da_duyet'],   'cls' => 'text-primary'],
    ['label' => 'Đã hoàn thành',        'value' => $stats['hoan_thanh'],    'params' => ['trang_thai' => 'hoan_thanh'], 'cls' => 'text-muted'],
];
$nhanTrangThai = [
    'cho_duyet' => ['Chờ duyệt', 'warning text-dark'],
    'da_duyet'  => ['Đã duyệt', 'primary'],
];
?>
<div class="container py-4">
    <!-- Lời chào -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <?= avatar($me['ho_ten'], 'sinh_vien', 'avatar-lg') ?>
        <div class="flex-grow-1">
            <h1 class="h3 mb-0">Xin chào, <?= e($me['ho_ten']) ?></h1>
            <p class="text-muted mb-0">Khu vực dành cho sinh viên</p>
        </div>
    </div>

    <!-- 3 ô thống kê -->
    <div class="row g-3 mb-4">
        <?php foreach ($tiles as $tile): ?>
            <div class="col-12 col-md-4">
                <a class="role-block d-block text-decoration-none shadow-sm h-100 p-3" href="<?= e(url('sinh-vien/phan-hoi', $tile['params'])) ?>">
                    <div class="small text-muted mb-1"><?= e($tile['label']) ?></div>
                    <div class="display-6 fw-bold <?= e($tile['cls']) ?>"><?= e($tile['value']) ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($stats['chua_danh_gia'] > 0): ?>
        <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-2" role="alert">
            <span>Bạn có <strong><?= e($stats['chua_danh_gia']) ?></strong> buổi tư vấn đã hoàn thành chưa đánh giá.</span>
            <a class="btn btn-sm btn-outline-primary" href="<?= e(url('sinh-vien/phan-hoi', ['trang_thai' => 'hoan_thanh'])) ?>">Đánh giá ngay</a>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Cuộc hẹn sắp tới -->
        <div class="col-lg-8">
            <h2 class="h5 section-title">Cuộc hẹn sắp tới</h2>
            <?php if (empty($upcoming)): ?>
                <p class="text-muted">Bạn chưa có cuộc hẹn nào sắp tới. <a href="<?= e(url('sinh-vien/dat-lich')) ?>">Đặt lịch với giảng viên →</a></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle app-table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Giảng viên</th>
                                <th>Chủ đề</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($upcoming as $a): ?>
                            <?php [$nhan, $mau] = $nhanTrangThai[$a['trang_thai']] ?? [$a['trang_thai'], 'secondary']; ?>
                            <tr>
                                <td class="small"><?= e(date('d/m H:i', strtotime($a['bat_dau']))) ?>–<?= e(date('H:i', strtotime($a['ket_thuc']))) ?></td>
                                <td><?= e($a['ten_giang_vien'] ?? '') ?></td>
                                <td><?= e($a['chu_de'] ?? '') ?></td>
                                <td><span class="badge bg-<?= e($mau) ?>"><?= e($nhan) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <a href="<?= e(url('sinh-vien/phan-hoi')) ?>">Xem tất cả lịch hẹn →</a>
        </div>

        <!-- Tài khoản + lối tắt -->
        <div class="col-lg-4">
            <h2 class="h5 section-title">Thông tin tài khoản</h2>
            <dl class="info-list">
                <div><dt>Email</dt><dd><?= e($me['email']) ?></dd></div>
                <div><dt>Đăng nhập gần nhất</dt><dd><?= $me['dang_nhap_lan_cuoi'] ? e(date('d/m/Y H:i', strtotime($me['dang_nhap_lan_cuoi']))) : 'Lần đầu' ?></dd></div>
            </dl>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary btn-sm" href="<?= e(url('tai-khoan/ho-so')) ?>">Chỉnh sửa hồ sơ</a>
                <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('tai-khoan/doi-mat-khau')) ?>">Đổi mật khẩu</a>
            </div>
            <a class="btn btn-primary w-100 fw-semibold mt-3 d-md-none" href="<?= e(url('sinh-vien/dat-lich')) ?>">+ Đặt lịch mới</a>
        </div>
    </div>
</div>